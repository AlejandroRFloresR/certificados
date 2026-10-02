<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TutorController extends Controller
{
    private const MAX_TUTORS_PER_COURSE = 3;

    public function index(): Response
    {
        $tutors = Tutor::whereHas('user')
            ->with(['user:id,name,email', 'courses:id,title'])
            ->get()
            ->map(fn (Tutor $t) => [
                'id'            => $t->id,
                'name'          => $t->user->name,
                'email'         => $t->user->email,
                'signature_url' => $this->signatureUrl($t),
                'courses'       => $t->courses->pluck('title'),
            ])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return Inertia::render('Admin/Tutors/Index', ['tutors' => $tutors]);
    }

    public function editCourses(Tutor $tutor): Response
    {
        $tutor->load('user:id,name', 'courses:id');

        return Inertia::render('Admin/Tutors/Courses', [
            'tutor'    => ['id' => $tutor->id, 'name' => $tutor->user->name ?? $tutor->name],
            'courses'  => Course::withCount('tutors')->orderBy('title')->get(['id', 'title'])
                ->map(fn ($c) => ['id' => $c->id, 'title' => $c->title, 'tutors_count' => $c->tutors_count]),
            'selected' => $tutor->courses->pluck('id'),
            'max'      => self::MAX_TUTORS_PER_COURSE,
        ]);
    }

    public function updateCourses(Request $request, Tutor $tutor)
    {
        $data = $request->validate([
            'courses'   => ['nullable', 'array'],
            'courses.*' => ['integer', 'exists:courses,id'],
        ]);

        $requested = array_values(array_unique($data['courses'] ?? []));
        $already   = $tutor->courses()->pluck('courses.id')->all();
        $toAdd     = array_diff($requested, $already);

        // Cursos nuevos que ya tienen el cupo completo
        $fullTitles = Course::withCount('tutors')
            ->whereIn('id', $toAdd)
            ->get()
            ->filter(fn ($c) => $c->tutors_count >= self::MAX_TUTORS_PER_COURSE)
            ->pluck('title')
            ->all();

        if ($fullTitles) {
            return back()->withErrors([
                'courses' => 'Estos cursos ya alcanzaron el máximo de ' . self::MAX_TUTORS_PER_COURSE
                    . ' tutores: ' . implode(', ', $fullTitles),
            ]);
        }

        $tutor->courses()->sync($requested);

        return redirect()->route('tutors.index')->with('success', 'Cursos actualizados correctamente.');
    }

    // ========== ADMIN: firma de cualquier tutor ==========

    public function editSignature(Tutor $tutor): Response
    {
        return $this->signaturePage($tutor, 'admin');
    }

    public function updateSignature(Request $request, Tutor $tutor)
    {
        $request->validate([
            'signature' => ['required', 'image', 'mimes:png', 'max:4096'],
        ]);

        $this->storeSignature($tutor, $request->file('signature'));

        return back()->with('success', 'Firma actualizada.');
    }

    // ========== TUTOR: su propia firma ==========

    public function editMySignature(Request $request): Response
    {
        $tutor = $request->user()->tutor;
        abort_if(!$tutor, 404, 'No sos tutor.');

        return $this->signaturePage($tutor, 'self');
    }

    public function updateMySignature(Request $request)
    {
        $tutor = $request->user()->tutor;
        abort_if(!$tutor, 404, 'No sos tutor.');

        $request->validate([
            'signature' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $this->storeSignature($tutor, $request->file('signature'));

        return back()->with('success', 'Firma actualizada.');
    }

    // ========== Helpers ==========

    /** Misma página para admin y tutor; cambian la URL de guardado, las reglas y el "Volver" */
    private function signaturePage(Tutor $tutor, string $mode): Response
    {
        $tutor->loadMissing('user:id,name');
        $isAdmin = $mode === 'admin';

        return Inertia::render('Tutors/Signature', [
            'mode'          => $mode,
            'tutorName'     => $tutor->user->name ?? $tutor->name,
            'signature_url' => $this->signatureUrl($tutor),
            'updateUrl'     => $isAdmin ? route('admin.tutors.signature.update', $tutor) : route('tutors.me.signature.update'),
            'backUrl'       => $isAdmin ? route('tutors.index') : route('dashboard'),
            'rules'         => $isAdmin
                ? ['accept' => '.png', 'hint' => 'Formato PNG, hasta 4 MB.']
                : ['accept' => '.png,.jpg,.jpeg,.webp', 'hint' => 'PNG, JPG o WEBP, hasta 2 MB.'],
        ]);
    }

    /** Guarda la firma nueva y borra la anterior */
    private function storeSignature(Tutor $tutor, UploadedFile $file): void
    {
        if ($tutor->signature && Storage::disk('public')->exists($tutor->signature)) {
            Storage::disk('public')->delete($tutor->signature);
        }

        $tutor->update(['signature' => $file->store('signatures', 'public')]);
    }

    private function signatureUrl(Tutor $tutor): ?string
    {
        return $tutor->signature ? asset('storage/' . $tutor->signature) : null;
    }
}

