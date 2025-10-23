<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function courses()
    {
        $courses=Course::withCount('users')->get();
        
        return view('admin.courses.index', compact('courses'));
    }

    public function courseUsers(Course $course)
    {
        // tutores del curso (para tu columna “Tutores”)
        $course->load('tutors');

        // usuarios del curso + SOLO sus certificados de ESTE curso
        $users = $course->users()
            ->with(['certificates' => function ($q) use ($course) {
                $q->where('course_id', $course->id)
                    ->select('id','user_id','course_id','certificate_code','type','snapshot_data'); // campos mínimos
            }])
            ->get();

        return view('admin.courses.users', compact('course','users'));
    }

      public function showForm()
    {
        $users=User::all();
        $courses=Course::all();

        return view('admin.issue-certificate', compact('users', 'courses'));
    }

}
