<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    
    public function create()
    {
        return view('courses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        Course::create($request->all());

    return redirect()->route('courses.index')->with('success','Curso creado correctamente.');
    }

    public function index()
    {
        $courses = Course::orderBy('start_date', 'desc')->get();
        return view('courses.index', compact('courses'));
    } 

    public function edit($id)
    {
        $course = Course::findOrFail($id);
        return view('courses.edit', compact('course'));
     }

     public function update(Request $request, $id)
     {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $course = Course::findOrFail($id);
        $course -> update($request->all());
        return redirect()->route('courses.index')->with('success','Curso actualizado correctamente.');
     }

     public function destroy($id)
     {
        $course = Course::findOrFail($id);
        $course -> delete();
        return redirect()->route('courses.index')->with('success', 'Curso eliminado.');
     }

     public function enroll(Course $course) 
     {
        $user=auth()->user();
        if(!$user->courses->contains($course->id)){
            $user->courses()->attach($course->id);
        }

        return redirect()->back()->with('success','Te inscribiste correctamente al curso');
     }

}
