<?php

namespace App\Http\Controllers;

use App\Jobs\SendCertificateEmail;
use App\Mail\CertificateIssuedMail;
use App\Models\Course;
use App\Exports\CourseUsersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Certificate;
use App\Services\CertificateIssuer;
use App\Services\CertificateTypeConflict;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;


class AdminController extends Controller
{
    public function exportCourseUsers(Course $course)
    {
        $filename = 'Alumnos_' . str_replace(' ','_',$course->title) . '_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new CourseUsersExport($course), $filename);
    }
     public function courseUsers(Course $course): Response
    {
        // Alumnos del curso + solo su certificado de ESTE curso
        $students = $course->users()
            ->with(['certificates' => fn ($q) => $q
                ->where('course_id', $course->id)
                ->select('id', 'user_id', 'course_id', 'certificate_code', 'type', 'snapshot_data', 'survey_token', 'emailed_at', 'email_error')
                ->withExists('surveyResponse')])
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                $cert = $user->certificates->first();

                return [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'dni'         => $user->dni,
                    'enrolled_at' => $user->pivot->created_at?->format('d/m/Y'),
                    'certificate' => $cert ? [
                        'type'         => $cert->type ?? data_get($cert->snapshot_data, 'type'),
                        'id'           => $cert->id,
                        'download_url' => route('certificates.download', $cert->certificate_code),
                        'emailed_at'   => $cert->emailed_at?->format('d/m/Y H:i'),
                        'email_error'  => $cert->email_error,
                        'survey'       => $cert->survey_token ? ($cert->survey_response_exists ? 'answered' : 'pending') : null,
                    ] : null,
                ];
            });

        return Inertia::render('Admin/Courses/Students', [
            'course'   => $course->adminSummary(),
            'students' => $students,
            'types'    => Certificate::TYPES,
        ]);
    }

    public function editCourseUsers(Course $course, Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));

        $items = User::query()
            ->select('id', 'name', 'email', 'dni', 'telefono')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('dni', 'like', "%{$q}%")))
            ->orderBy('name')
            ->limit(300)
            ->get();

        return Inertia::render('Admin/Courses/Assign', [
            'mode'     => 'users',
            'course'   => $course->only('id', 'title'),
            'items'    => $items,
            // TODOS los inscriptos, no solo los que aparecen en la búsqueda
            'selected' => $course->users()->pluck('users.id'),
            'q'        => $q,
        ]);
    }

    public function updateCourseUsers(Course $course, Request $request)
    {
        $data = $request->validate([
            'users'   => ['nullable','array'],
            'users.*' => ['integer','exists:users,id'],
        ]);

        // Si no vino 'users', interpretamos como “ninguno seleccionado”
        $ids = $data['users'] ?? [];

        // Sincroniza el pivot course_user (agrega/quita de una)
        $course->users()->sync($ids);

        return redirect()
            ->route('admin.courses.users', $course)
            ->with('success', 'Alumnos del curso actualizados.');

    }

    /** Emite el mismo tipo de certificado a varios alumnos del curso a la vez */
    public function issueCertificates(Course $course, Request $request, CertificateIssuer $issuer)
    {
        $data = $request->validate([
            'user_ids'   => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer'],
            'type'       => ['required', Rule::in(Certificate::TYPES)],
            'notify'     => ['sometimes', 'boolean'],
        ], ['user_ids.required' => 'Seleccioná al menos un alumno.']);
        $notify = $request->boolean('notify', true);

        // Solo alumnos inscriptos en ESTE curso y que todavía no tienen certificado
        $users = $course->users()
            ->whereIn('users.id', $data['user_ids'])
            ->whereDoesntHave('certificates', fn ($q) => $q->where('course_id', $course->id))
            ->get();

        $issued = 0;
        $conflicts = [];

        foreach ($users as $user) {
            try {
                DB::transaction(fn () => $issuer->issue($user, $course, $data['type'], $notify));
                $issued++;
            } catch (CertificateTypeConflict) {
                // Alguien emitió otro tipo justo entre la carga de la página y este pedido
                $conflicts[] = $user->name;
            }
        }

        $label = ['asistio' => 'Asistió', 'dicto' => 'Dictó', 'aprobado' => 'Aprobado'][$data['type']];
        $redirect = redirect()->route('admin.courses.users', $course)
            ->with('success', $issued === 1
                ? "Se emitió 1 certificado de tipo \"{$label}\"."
                : "Se emitieron {$issued} certificados de tipo \"{$label}\".");

        return $conflicts
            ? $redirect->with('error', 'No se emitió para: ' . implode(', ', $conflicts) . ' (ya tienen un certificado de otro tipo).')
            : $redirect;
    }

    /** Reenvía el certificado por correo a su titular */
    public function resendCertificate(Certificate $certificate)
    {
        abort_unless($certificate->user, 404);

        SendCertificateEmail::dispatchSync($certificate->id);
        $certificate->refresh();

        return back()->with(
            $certificate->email_error ? 'error' : 'success',
            $certificate->email_error
                ? "No se pudo enviar a {$certificate->user->email}: {$certificate->email_error}"
                : "Certificado enviado a {$certificate->user->email}."
        );
    }

    /** Muestra el correo tal como lo recibe el alumno (sin enviarlo) */
    public function previewCertificateEmail(Certificate $certificate)
    {
        return new CertificateIssuedMail($certificate);
    }
}
