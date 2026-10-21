<?php

namespace App\Http\Controllers;

use App\Exports\MoodleUsersExport;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use App\Services\CertificateIssuer;
use App\Services\CertificateTypeConflict;
use App\Services\MoodleGradeReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class MoodleController extends Controller
{
    /** Filas que se pueden emitir al confirmar */
    private const ACTIONABLE = ['issue', 'enroll_and_issue'];

    public function export(Course $course)
    {
        $filename = 'moodle_' . Str::slug($course->title) . '_' . now()->format('Ymd') . '.csv';

        return Excel::download(new MoodleUsersExport($course), $filename, ExcelFormat::CSV);
    }

    public function create(Course $course): Response
    {
        return Inertia::render('Admin/Courses/MoodleImport', [
            'course'  => $course->only('id', 'title', 'moodle_shortname'),
            'types'   => Certificate::TYPES,
            'preview' => session($this->sessionKey($course)),
        ]);
    }

    /** Lee el archivo, cruza con los usuarios y guarda la vista previa en la sesión */
    public function preview(Request $request, Course $course, MoodleGradeReader $reader)
    {
        $data = $request->validate([
            'file'      => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
            'type'      => ['required', Rule::in(Certificate::TYPES)],
            'min_grade' => ['nullable', 'numeric', 'min:0'],
        ]);

        $rows     = $reader->read($request->file('file'));
        $minGrade = isset($data['min_grade']) ? (float) $data['min_grade'] : null;
        $type     = $data['type'];

        // Todos los usuarios posibles en 1 consulta, para no consultar fila por fila
        $dnis   = collect($rows)->pluck('dni')->filter()->unique()->values();
        $emails = collect($rows)->pluck('email')->filter()->unique()->values();
        $users  = User::whereIn('dni', $dnis)->orWhereIn('email', $emails)->get(['id', 'name', 'email', 'dni']);

        $byDni    = $users->keyBy('dni');
        $byEmail  = $users->keyBy(fn ($u) => strtolower($u->email));
        $enrolled = $course->users()->pluck('users.id')->flip();
        $certs    = Certificate::where('course_id', $course->id)->pluck('type', 'user_id');

        $result = collect($rows)->map(function ($r) use ($byDni, $byEmail, $enrolled, $certs, $minGrade, $type) {
            $user = ($r['dni'] ? $byDni->get($r['dni']) : null)
                ?? ($r['email'] ? $byEmail->get($r['email']) : null);

            $status = match (true) {
                !$user                                                        => 'not_found',
                $minGrade !== null && ($r['grade'] ?? -1) < $minGrade         => 'below_grade',
                $certs->has($user->id) && $certs[$user->id] === $type         => 'already_issued',
                $certs->has($user->id)                                        => 'type_conflict',
                !$enrolled->has($user->id)                                    => 'enroll_and_issue',
                default                                                       => 'issue',
            };

            return [
                'row'     => $r['row'],
                'user_id' => $user?->id,
                'name'    => $user->name ?? ($r['name'] ?: '—'),
                'dni'     => $user->dni ?? $r['dni'],
                'email'   => $user->email ?? $r['email'],
                'grade'   => $r['grade'],
                'status'  => $status,
            ];
        })
            // Un mismo alumno repetido en el archivo cuenta una sola vez
            ->unique(fn ($r) => $r['user_id'] ?? 'row-' . $r['row'])
            ->values()
            ->all();

        session()->put($this->sessionKey($course), [
            'file'      => $request->file('file')->getClientOriginalName(),
            'type'      => $type,
            'min_grade' => $minGrade,
            'has_grade' => $reader->hasGradeColumn($request->file('file')),
            'rows'      => $result,
        ]);

        return redirect()->route('admin.courses.moodle', $course);
    }

    /** Emite los certificados de las filas elegidas */
    public function confirm(Request $request, Course $course, CertificateIssuer $issuer)
    {
        $preview = session($this->sessionKey($course));

        if (!$preview) {
            return redirect()->route('admin.courses.moodle', $course)
                ->with('error', 'La vista previa expiró. Volvé a subir el archivo.');
        }

        $data = $request->validate([
            'user_ids'   => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer'],
        ], ['user_ids.required' => 'Seleccioná al menos un alumno.']);

        // Solo se emite lo que la vista previa marcó como emitible (no se confía en lo que manda el navegador)
        $actionable = collect($preview['rows'])
            ->whereIn('status', self::ACTIONABLE)
            ->keyBy('user_id')
            ->only($data['user_ids']);

        $users = User::whereIn('id', $actionable->keys())->get();
        $course->loadMissing('tutors');

        $issued = 0;
        $enrolledNow = 0;
        $conflicts = [];

        foreach ($users as $user) {
            try {
                DB::transaction(function () use ($user, $course, $actionable, $issuer, $preview, &$issued, &$enrolledNow) {
                    if ($actionable[$user->id]['status'] === 'enroll_and_issue') {
                        $course->users()->syncWithoutDetaching([$user->id]);
                        $enrolledNow++;
                    }
                    $issuer->issue($user, $course, $preview['type']);
                    $issued++;
                });
            } catch (CertificateTypeConflict) {
                // Alguien emitió otro tipo entre la vista previa y la confirmación
                $conflicts[] = $user->name;
            }
        }

        session()->forget($this->sessionKey($course));

        $message = "Se emitieron {$issued} certificados.";
        if ($enrolledNow) {
            $message .= " {$enrolledNow} alumnos se inscribieron al curso automáticamente.";
        }

        $redirect = redirect()->route('admin.courses.users', $course)->with('success', $message);

        return $conflicts
            ? $redirect->with('error', 'No se emitió para: ' . implode(', ', $conflicts) . ' (ya tienen un certificado de otro tipo).')
            : $redirect;
    }

    /** Descarta la vista previa para subir otro archivo */
    public function cancel(Course $course)
    {
        session()->forget($this->sessionKey($course));

        return redirect()->route('admin.courses.moodle', $course);
    }

    private function sessionKey(Course $course): string
    {
        return "moodle_preview.{$course->id}";
    }
}
