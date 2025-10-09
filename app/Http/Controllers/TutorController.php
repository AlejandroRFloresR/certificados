<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tutor;
use App\Models\Course;

class TutorController extends Controller
{   
    public function index()
    {
        $tutors= Tutor::with('courses', 'user')->get();
    
        return view('tutors.index',compact('tutors'));
    }

    public function create()
   {
    // En vez de crear un tutor suelto, llevamos al alta de Usuario con rol=tutor
        return redirect()
        ->route('admin.users.create', ['role' => 'tutor'])
        ->with('info', 'Para crear un Tutor, primero creá un Usuario con rol tutor.');
    }

    public function store(Request $request)
    {
    // Bloqueamos creación directa de tutores sueltos
    return redirect()
        ->route('admin.users.create', ['role' => 'tutor'])
        ->with('info', 'Para crear un Tutor, primero creá un Usuario con rol tutor.');
    }

    public function updateCourses(Request $request, Tutor $tutor)
    {
        $request->validate([
            'courses' => 'nullable|array',
            'courses.*' => 'exists:courses,id'
        ]);
        $tutor->courses()->sync($request->courses ?? []);
        return redirect()->route('tutors.index')->with('success', 'Cursos actualizados correctamente');
    }

    public function editCourses(Tutor $tutor)
    {
        $courses = Course::all();
        $tutor->load('courses');
        return view('tutors.edit-courses', compact ('tutor', 'courses'));

    }
}

