<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\AdminController;
use App\Models\Certificate;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
  //  Route::get('/admin-panel', [AdminController::class, 'index']);
    
    Route::get('/courses/{id}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{id}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');

    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users/{user}/assign-role', [AdminUserController::class, 'assignRole'])->name('admin.users.assignRole');

    Route::get('/admin/courses',[AdminController::class, 'courses'])->name('admin.courses');
    Route::get('/admin/courses',[AdminController::class, 'showForm'])->name('admin.courses');
    Route::get('/admin/courses/{course}/users',[AdminController::class, 'courseUsers'])->name('admin.course.users');

    Route::post('/certificates/issue', [CertificateController::class, 'issue'])->name('certificates.issue');
});

    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    

    Route::get('/dashboard', function () {
        return view('dashboard');
        })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/courses/{course}/certificate',[CertificateController::class,'generate'])->name('courses.certificate');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('courses/{course}/enroll}', [CourseController::class, 'enroll'])->name('courses.enroll');
});

require __DIR__.'/auth.php';
