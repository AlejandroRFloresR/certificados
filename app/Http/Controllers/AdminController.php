<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Exports\CourseUsersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tutor;

class AdminController extends Controller
{
    public function courses()
    {
        $courses=Course::withCount('users')->get();
        
        return view('admin.courses.index', compact('courses'));
    }

    public function exportCourseUsers(Course $course)
    {
        $filename = 'Alumnos_' . str_replace(' ','_',$course->title) . '_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new CourseUsersExport($course), $filename);
    }
    public function courseUsers(Course $course)
    {
        // tutores del curso (para tu columna “Tutores”)
        $course->load('tutors');

        // usuarios del curso + SOLO sus certificados de ESTE curso
        $users = $course->users()
            ->with(['certificates' => function ($q) use ($course) {
                $q->where('course_id', $course->id)
                    ->select('id','user_id','course_id','certificate_code','type','snapshot_data'); // campos mínimos
            }])
            ->get();

        return view('admin.courses.users', compact('course','users'));
    }

      public function showForm()
    {
        $users=User::all();
        $courses=Course::all();

        return view('admin.issue-certificate', compact('users', 'courses'));
    }

    public function editCourseUsers(Course $course, Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $usersQuery = User::query()
            ->select('id','name','email','dni','telefono')
            ->orderBy('name');

        if ($q !== '') {
            $usersQuery->where(function($w) use ($q) {
                $w->where('name','like',"%{$q}%")
                ->orWhere('email','like',"%{$q}%")
                ->orWhere('dni','like',"%{$q}%");
            });
        }

        // Traemos una lista razonable; podés usar paginate si hay muchos
        $users = $usersQuery->limit(300)->get();

        // IDs actualmente inscriptos
        $enrolledIds = $course->users()->pluck('users.id')->all();

        return view('admin.courses.users-edit', [
            'course'      => $course,
            'users'       => $users,
            'enrolledIds' => $enrolledIds,
            'q'           => $q,
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
    public function editCourseTutors(Course $course, Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $tutorsQuery = Tutor::query()
            ->with('user:id,name,email') // opcional
            ->orderBy('name');

        if ($q !== '') {
            $tutorsQuery->where('name','like',"%{$q}%")
                ->orWhereHas('user', function ($w) use ($q) {
                    $w->where('name','like',"%{$q}%")
                    ->orWhere('email','like',"%{$q}%");
                });
        }

        $tutors = $tutorsQuery->limit(300)->get();
        $selected = $course->tutors()->pluck('tutors.id')->all();

        return view('admin.courses.tutors-edit', [
            'course'   => $course,
            'tutors'   => $tutors,
            'selected' => $selected,
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
