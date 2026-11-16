<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /** Cuántos cursos disponibles se muestran en el inicio (el resto, en el catálogo) */
    private const AVAILABLE_LIMIT = 6;

    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        // El admin no es alumno: su inicio es la gestión de cursos
        if ($user->hasRole('admin')) {
            return redirect()->route('courses.index');
        }

        // Certificados del usuario, indexados por curso
        $certsByCourse = $user->certificates()
            ->select('id', 'course_id', 'certificate_code', 'type', 'issued_date', 'snapshot_data')
            ->get()
            ->keyBy('course_id');

        // Cursos donde está inscripto como alumno
        $myCourses = $user->courses()
            ->orderBy('start_date', 'desc')
            ->get();

        $studentCourses = $myCourses
            ->map(function ($course) use ($certsByCourse) {
                $cert = $certsByCourse->get($course->id);

                return [
                    'id'          => $course->id,
                    'title'       => $course->title,
                    'modality'    => $course->modality,
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

        // Cursos a los que se puede inscribir: publicados, no finalizados y donde todavía no está
        $statusOrder = ['en_curso' => 0, 'proximo' => 1, 'sin_fecha' => 2];

        $available = Course::public()
            ->with('category')
            ->whereNotIn('id', $myCourses->pluck('id'))
            ->get()
            ->filter(fn (Course $c) => $c->status !== 'finalizado')
            ->sortBy(fn (Course $c) => [$statusOrder[$c->status] ?? 9, $c->start_date ?? '9999-12-31'])
            ->values();

        return Inertia::render('Dashboard', [
            'studentCourses'   => $studentCourses,
            'availableCourses' => $available->take(self::AVAILABLE_LIMIT)->map(fn (Course $c) => [
                'id'         => $c->id,
                'title'      => $c->title,
                'category'   => $c->category?->name,
                'modality'   => $c->modality,
                'location'   => $c->location,
                'start_date' => $c->start_date,
                'end_date'   => $c->end_date,
                'status'     => $c->status,
            ])->values(),
            'availableTotal' => $available->count(),
            'modalities'     => Course::MODALITIES,
        ]);
    }
}
