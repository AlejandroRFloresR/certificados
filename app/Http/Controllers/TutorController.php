<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tutor;
use App\Models\Course;

class TutorController extends Controller
{   
    public function index()
    {
        $tutors= Tutor::whereHas('user')
        ->with(['user:id,name,email','courses:id,title'])
        ->get();
    
        return view('tutors.index',compact('tutors'));
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

