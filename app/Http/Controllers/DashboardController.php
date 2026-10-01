<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        // Certificados del usuario, indexados por curso
        $certsByCourse = $user->certificates()
            ->select('id', 'course_id', 'certificate_code', 'type', 'issued_date', 'snapshot_data')
            ->get()
            ->keyBy('course_id');

        // Cursos donde está inscripto como alumno
        $studentCourses = $user->courses()
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($course) use ($certsByCourse) {
                $cert = $certsByCourse->get($course->id);

                return [
                    'id'          => $course->id,
                    'title'       => $course->title,
                    'hours'       => $course->hours,
                    'start_date'  => $course->start_date,
                    'end_date'    => $course->end_date,
                    'status'      => $course->status,
                    'certificate' => $cert ? [
                        'code'         => $cert->certificate_code,
                        'type'         => $cert->type ?? data_get($cert->snapshot_data, 'type'),
                        'issued'       => $cert->issued_date ? Carbon::parse($cert->issued_date)->format('d/m/Y') : null,
                        'download_url' => route('certificates.download', $cert->certificate_code),
                    ] : null,
                ];
            })
            ->values();

        // Cursos donde es tutor (si tiene el rol y un tutor asociado)
        $tutorCourses = [];
        if ($user->hasRole('tutor') && $user->tutor) {
            $tutorCourses = $user->tutor->courses()
                ->withCount('users')
                ->orderBy('start_date', 'desc')
                ->get()
                ->map(fn ($course) => [
                    'id'         => $course->id,
                    'title'      => $course->title,
                    'hours'      => $course->hours,
                    'start_date' => $course->start_date,
                    'end_date'   => $course->end_date,
                    'status'     => $course->status,
                    'students'   => $course->users_count,
                ])
                ->values();
        }

        return Inertia::render('Dashboard', [
            'studentCourses' => $studentCourses,
            'tutorCourses'   => $tutorCourses,
            'isTutor'        => $user->hasRole('tutor'),
        ]);
    }
}
