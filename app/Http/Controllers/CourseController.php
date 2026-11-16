<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Signer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // El listado es la gestión de cursos: el alumno usa su inicio y el catálogo
        if (!$request->user()->hasRole('admin')) {
            return redirect()->route('dashboard');
        }

        $courses = Course::with('category')
            ->withCount(['users', 'certificates' => fn ($q) => $q->whereNotNull('user_id')])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn (Course $course) => [
                'id'           => $course->id,
                'title'        => $course->title,
                'category'     => $course->category?->name,
                'start_date'   => $course->start_date,
                'end_date'     => $course->end_date,
                'status'       => $course->status,
                'is_public'    => $course->is_public,
                'students'     => $course->users_count,
                'certificates' => $course->certificates_count,
            ]);

        return Inertia::render('Courses/Index', ['courses' => $courses]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(Request $request)
    {
        [$data, $signers] = $this->validated($request);
        $course = Course::create($data);
        $this->syncSigners($course, $signers);

        return redirect()->route('admin.courses.users', $course)->with('success', 'Curso creado correctamente.');
    }

    public function edit($id): Response
    {
        return $this->form(Course::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        [$data, $signers] = $this->validated($request);
        $course->update($data);
        $this->syncSigners($course, $signers);

        return redirect()->route('admin.courses.users', $course)->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy($id)
    {
        Course::findOrFail($id)->delete();

        return redirect()->route('courses.index')->with('success', 'Curso eliminado.');
    }

    public function enroll(Course $course)
    {
        // Solo cursos publicados en el catálogo y que no terminaron
        if (!$course->is_public || $course->status === 'finalizado') {
            return redirect()->back()->with('error', 'Este curso no tiene la inscripción abierta.');
        }

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
            'course' => $course ? [
                ...$course->only(['id', 'title', 'description', 'start_date', 'end_date', 'modality', 'location', 'is_public']),
                'category' => $course->category?->name,
            ] : null,
            'modalities' => Course::MODALITIES,
            'categories' => Category::orderBy('name')->pluck('name'),
            'signers'    => Signer::orderBy('name')->get()
                ->map(fn (Signer $s) => $s->only('id', 'name', 'position') + ['signature_url' => $s->signature_url]),
            'selectedSigners' => $course ? $course->signers->pluck('id') : [],
            'maxSigners' => Signer::MAX_PER_COURSE,
        ]);
    }

    /** Guarda qué firmantes lleva el curso, en el orden elegido (null = el pedido no los incluía) */
    private function syncSigners(Course $course, ?array $ids): void
    {
        if ($ids === null) {
            return;
        }

        $course->signers()->sync(
            collect($ids)->values()->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i]])->all()
        );
    }

    /**
     * Valida todo junto (si algo falla no se guarda nada) y devuelve
     * [datos del curso, ids de firmantes]. La categoría pasa de nombre a category_id.
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'category'    => ['nullable', 'string', 'max:100'],
            'modality'    => ['nullable', Rule::in(array_keys(Course::MODALITIES))],
            'location'    => ['nullable', 'string', 'max:255'],
            'is_public'   => ['boolean'],
            'signers'     => ['sometimes', 'array', 'max:' . Signer::MAX_PER_COURSE],
            'signers.*'   => ['integer', 'distinct', 'exists:signers,id'],
        ], [
            'end_date.after_or_equal' => 'No puede ser anterior a la fecha de inicio.',
            'signers.max'             => 'Un certificado puede llevar como máximo ' . Signer::MAX_PER_COURSE . ' firmas.',
        ]);

        $signers = $request->has('signers') ? ($data['signers'] ?? []) : null;
        unset($data['signers']);

        $data['category_id'] = Category::resolve($data['category'] ?? null)?->id;
        unset($data['category']);

        return [$data, $signers];
    }
}
