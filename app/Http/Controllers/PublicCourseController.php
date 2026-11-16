<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
            ->with('category')
            ->get()
            ->map(fn (Course $course) => [
                'id'          => $course->id,
                'title'       => $course->title,
                'description' => $course->description,
                'category'    => $course->category?->name,
                'modality'    => $course->modality,
                'location'    => $course->location,
                'start_date'  => $course->start_date,
                'end_date'    => $course->end_date,
                'status'      => $course->status,
                'enrolled'    => in_array($course->id, $enrolledIds),
                'enroll_url'  => route('courses.enroll', $course->id),
            ])
            ->values();

        return Inertia::render('Catalog', [
            'courses'    => $courses,
            'modalities' => Course::MODALITIES,
        ]);
    }
}
