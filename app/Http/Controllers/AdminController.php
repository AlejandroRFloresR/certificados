<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Exports\CourseUsersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Certificate;
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
        $course->load('tutors.user');

        // Alumnos del curso + solo su certificado de ESTE curso
        $students = $course->users()
            ->with(['certificates' => fn ($q) => $q
                ->where('course_id', $course->id)
                ->select('id', 'user_id', 'course_id', 'certificate_code', 'type', 'snapshot_data')])
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
                        'download_url' => route('certificates.download', $cert->certificate_code),
                    ] : null,
                ];
            });

        return Inertia::render('Admin/Courses/Students', [
            'course' => [
                'id'     => $course->id,
                'title'  => $course->title,
                'tutors' => $course->tutors->map(fn ($t) => optional($t->user)->name ?? $t->name)->values(),
            ],
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
    public function editCourseTutors(Course $course, Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));

        $items = Tutor::query()
            ->with('user:id,name,email')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhereHas('user', fn ($u) => $u
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%"))))
            ->orderBy('name')
            ->limit(300)
            ->get()
            ->map(fn ($t) => [
                'id'    => $t->id,
                'name'  => $t->user->name ?? $t->name,
                'email' => $t->user->email ?? null,
            ]);

        return Inertia::render('Admin/Courses/Assign', [
            'mode'     => 'tutors',
            'course'   => $course->only('id', 'title'),
            'items'    => $items,
            'selected' => $course->tutors()->pluck('tutors.id'),
            'q'        => $q,
        ]);
    }
    public function updateCourseTutors(Course $course, Request $request)
    {
        $data = $request->validate([
            'tutors'   => ['nullable','array','max:3'], // 💡 valida cantidad
            'tutors.*' => ['integer','exists:tutors,id'],
        ], [
            'tutors.max' => 'Cada curso puede tener como máximo 3 tutores.',
        ]);

        $ids = $data['tutors'] ?? [];

        // Seguridad extra: por si alguien burla el front
        if (count($ids) > 3) {
            return back()->withErrors(['tutors' => 'Máximo 3 tutores por curso.'])->withInput();
        }

        // Sincronizar pivot
        $course->tutors()->sync($ids);

        return redirect()
            ->route('admin.courses.users', $course)
            ->with('success', 'Tutores del curso actualizados (máx. 3).');
    }

}
