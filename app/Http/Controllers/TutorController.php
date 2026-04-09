<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tutor;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;

class TutorController extends Controller
{   
    public function index()
    {
        $tutors= Tutor::whereHas('user')
        ->with(['user:id,name,email','courses:id,title'])
        ->get();
    
        return view('tutors.index',compact('tutors'));
    }

    private const MAX_TUTORS_PER_COURSE = 3;

    public function editCourses(Tutor $tutor)
        {
            // Traemos el contador de tutores por curso para poder deshabilitar los llenos
            $courses = \App\Models\Course::withCount('tutors')->orderBy('title')->get();
            $tutor->load('courses:id'); // para saber cuáles ya tiene
            return view('tutors.edit-courses', compact('tutor','courses'));
        }

        public function updateCourses(Request $request, Tutor $tutor)
        {
            $data = $request->validate([
                'courses'   => ['nullable','array'],
                'courses.*' => ['integer','exists:courses,id'],
            ]);

            $requested = array_values(array_unique($data['courses'] ?? [])); // normalizar
            $already   = $tutor->courses()->pluck('courses.id')->all();      // cursos que ya tenía
            $toAdd     = array_diff($requested, $already);                    // altas nuevas

            // Cargamos counts de los cursos a los que quiere entrar
            $coursesInfo = \App\Models\Course::withCount('tutors')
                ->whereIn('id', $toAdd)
                ->get()
                ->keyBy('id');

            // Verificamos cuáles ya están llenos (>= MAX y el tutor no estaba)
            $fullTitles = [];
            foreach ($toAdd as $courseId) {
                $c = $coursesInfo[$courseId] ?? null;
                if ($c && $c->tutors_count >= self::MAX_TUTORS_PER_COURSE) {
                    $fullTitles[] = $c->title;
                }
            }

            if ($fullTitles) {
                return back()
                    ->withErrors([
                        'courses' => 'Estos cursos ya alcanzaron el máximo de '
                                . self::MAX_TUTORS_PER_COURSE
                                . ' tutores: ' . implode(', ', $fullTitles),
                    ])
                    ->withInput();
            }

            // Si todo OK, sincronizamos (agrega y quita en una sola llamada)
            $tutor->courses()->sync($requested);

            return redirect()
                ->route('tutors.index')
                ->with('success', 'Cursos actualizados correctamente.');
        }
    public function editSignature(Tutor $tutor)
    {
        // opcional: cargar user por si querés mostrar el nombre desde allí
        $tutor->load('user:id,name,email');
        return view('tutors.signature', [
            'tutor' => $tutor,
            'action' => route('admin.tutors.signature.update', $tutor),
            'title' => 'Firma del tutor — ' . ($tutor->user->name ?? $tutor->name),
        ]);
    }

    public function updateSignature(Request $request, Tutor $tutor)
    {
        $data = $request->validate([
            'signature' => ['required','image','mimes:png','max:4096'], // ~2MB
        ]);

        // borrar archivo anterior si existe
        if ($tutor->signature && Storage::disk('public')->exists($tutor->signature)) {
            Storage::disk('public')->delete($tutor->signature);
        }

        // guardar nuevo
        $path = $request->file('signature')->store('signatures', 'public');

        $tutor->update(['signature' => $path]);

        return back()->with('success','Firma actualizada.');
    }

    // ========== TUTOR (self-service) ==========
    public function editMySignature(Request $request)
    {
        $tutor = $request->user()->tutor; // relación hasOne en User
        abort_if(!$tutor, 404, 'No sos tutor.');

        return view('tutors.signature', [
            'tutor' => $tutor,
            'action' => route('tutors.me.signature.update'),
            'title' => 'Mi firma',
        ]);
    }

    public function updateMySignature(Request $request)
    {
        $tutor = $request->user()->tutor;
        abort_if(!$tutor, 404, 'No sos tutor.');

        $data = $request->validate([
            'signature' => ['required','image','mimes:png,jpg,jpeg,webp','max:2048'],
        ]);

        if ($tutor->signature && Storage::disk('public')->exists($tutor->signature)) {
            Storage::disk('public')->delete($tutor->signature);
        }

        $path = $request->file('signature')->store('signatures', 'public');

        $tutor->update(['signature' => $path]);

        return back()->with('success','Firma actualizada.');
    }
}

