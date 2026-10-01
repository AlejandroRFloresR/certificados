<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $enrolledIds = $request->user()->courses()->pluck('courses.id')->all();

        $courses = Course::with('tutors.user')
            ->withCount('users')
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn (Course $course) => [
                'id'         => $course->id,
                'title'      => $course->title,
                'category'   => $course->category,
                'hours'      => $course->hours,
                'start_date' => $course->start_date,
                'end_date'   => $course->end_date,
                'status'     => $course->status,
                'is_public'  => $course->is_public,
                'students'   => $course->users_count,
                'tutors'     => $course->tutors
                    ->map(fn ($tutor) => optional($tutor->user)->name ?? $tutor->name)
                    ->filter()
                    ->values(),
                'enrolled'   => in_array($course->id, $enrolledIds),
            ]);

        return Inertia::render('Courses/Index', ['courses' => $courses]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(Request $request)
    {
        Course::create($request->validate($this->rules()));

        return redirect()->route('courses.index')->with('success', 'Curso creado correctamente.');
    }

    public function edit($id): Response
    {
        return $this->form(Course::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        Course::findOrFail($id)->update($request->validate($this->rules()));

        return redirect()->route('courses.index')->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy($id)
    {
        Course::findOrFail($id)->delete();

        return redirect()->route('courses.index')->with('success', 'Curso eliminado.');
    }

    public function enroll(Course $course)
    {
        $user = auth()->user();
        if (!$user->courses->contains($course->id)) {
            $user->courses()->attach($course->id);
        }

        return redirect()->back()->with('success', 'Te inscribiste correctamente al curso');
    }

    /** Misma página para crear y editar */
    private function form(?Course $course): Response
    {
        return Inertia::render('Courses/Form', [
            'course' => $course?->only([
                'id', 'title', 'description', 'start_date', 'end_date',
                'hours', 'category', 'modality', 'location', 'is_public',
            ]),
            'modalities' => Course::MODALITIES,
            'categories' => Course::whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    private function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'hours'       => ['nullable', 'integer', 'min:1', 'max:2000'],
            'category'    => ['nullable', 'string', 'max:100'],
            'modality'    => ['nullable', Rule::in(array_keys(Course::MODALITIES))],
            'location'    => ['nullable', 'string', 'max:255'],
            'is_public'   => ['boolean'],
        ];
    }
}
