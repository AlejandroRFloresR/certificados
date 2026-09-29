<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class PublicCourseController extends Controller
{
    /**
     * Catálogo público de cursos (página guest montada con React).
     */
    public function index()
    {
        $user = Auth::user();

        // Si un invitado inicia sesión desde el catálogo, vuelve acá
        if (!$user) {
            redirect()->setIntendedUrl(route('catalog.index'));
        }

        $enrolledIds = $user ? $user->courses()->pluck('courses.id')->all() : [];

        $courses = Course::public()
            ->with('tutors.user')
            ->get()
            ->map(fn (Course $course) => [
                'id'          => $course->id,
                'title'       => $course->title,
                'description' => $course->description,
                'hours'       => $course->hours,
                'category'    => $course->category,
                'modality'    => $course->modality,
                'location'    => $course->location,
                'start_date'  => $course->start_date,
                'end_date'    => $course->end_date,
                'status'      => $course->status,
                'tutors'      => $course->tutors
                    ->map(fn ($tutor) => optional($tutor->user)->name ?? $tutor->name)
                    ->filter()
                    ->values(),
                'enrolled'    => in_array($course->id, $enrolledIds),
                'enroll_url'  => route('courses.enroll', $course->id),
            ])
            ->values();

        $props = [
            'courses'     => $courses,
            'modalities'  => Course::MODALITIES,
            'auth'        => (bool) $user,
            'loginUrl'    => route('login'),
            'registerUrl' => Route::has('register') ? route('register') : null,
            'csrf'        => csrf_token(),
            'flash'       => session('success'),
        ];

        return view('courses.catalog', compact('props'));
    }
}
