<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tutor;
use App\Models\Course;

class TutorController extends Controller
{   
    public function index()
    {
        $tutors= Tutor::with('courses')->get();
    
        return view('tutors.index',compact('tutors'));
    }

    public function create()
    { 
    $courses= Course::all();
     return view('tutors.create', compact('courses'));   
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'=> 'required|string|max:255',
            'signature'=> 'required|image|max:2048',
            'courses'=> 'nullable|array',
            'courses.*'=> 'exists:courses,id'
        ]);

        //Guardar la Firma

        $path= $request->file('signature')->store('signatures', 'public');

        //Crear Tutor
        $tutor=Tutor::create([
            'name'=> $request->name,
            'signature'=> $path,
        ]);
        //Asignar Cursos
        if($request->has('courses'))
        {
            $tutor->courses()->sync($request->courses);
        }

        
        return redirect()->route('tutors.index')->with('success', 'Tutor guardado con exito');
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

