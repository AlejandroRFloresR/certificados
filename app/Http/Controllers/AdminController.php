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
        $users=$course->users()->get();

        return view('admin.courses.users', compact('course', 'users'));
    }

      public function showForm()
    {
        $users=User::all();
        $courses=Course::all();

        return view('admin.issue-certificate', compact('users', 'courses'));
    }

}
