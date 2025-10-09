<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TutorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
});

/**
 * TUTORS
 * (Tip: evaluá proteger estas rutas con auth/role si no deben ser públicas)
 */
Route::get('/tutors/{tutor}/edit-courses', [TutorController::class, 'editCourses'])->name('tutors.editCourses');
Route::put('/tutors/{tutor}/update-courses', [TutorController::class, 'updateCourses'])->name('tutors.updateCourses');
Route::get('/tutors/create', [TutorController::class, 'create'])->name('tutors.create');
Route::post('/tutors', [TutorController::class, 'store'])->name('tutors.store');
Route::get('/tutors', [TutorController::class, 'index'])->name('tutors.index');

/**
 * ADMIN (prefijo URL /admin y nombre admin.*)
 */
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

        // Cursos (gestión admin con CourseController)
        Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{id}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{id}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');

        // Panel/AdminController (evitamos duplicar misma URL y name)
        Route::get('/courses', [AdminController::class, 'courses'])->name('courses');
        Route::get('/courses/form', [AdminController::class, 'showForm'])->name('courses.form');
        Route::get('/courses/{course}/users', [AdminController::class, 'courseUsers'])->name('courses.users');

        // Usuarios (panel admin)
        Route::resource('users', AdminUserController::class)->except(['show']);
        // Acciones extra coherentes con kebab-case
        Route::post('users/{user}/assign-role', [AdminUserController::class, 'assignRole'])->name('users.assign-role');
        Route::get('users/{user}/password', [AdminUserController::class, 'editPassword'])->name('users.edit-password');
        Route::put('users/{user}/password', [AdminUserController::class, 'updatePassword'])->name('users.update-password');
    });

/**
 * Cursos (público / general)
 */
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');

/**
 * Dashboard
 */
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/**
 * Rutas autenticadas (usuarios logueados)
 */
Route::middleware('auth')->group(function () {
    Route::get('/courses/{course}/certificate/{user}', [CertificateController::class, 'generate'])->name('courses.certificate');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // FIX: se quitó la "}" extra en la URL
    Route::post('courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
});

/**
 * Verificación de certificados por código
 */
Route::get('/verify/{code}', function ($code) {
    $cert = \App\Models\Certificate::where('certificate_code', $code)->firstOrFail();
    return response()->json([
        'valid'       => true,
        'user_id'     => $cert->user_id,
        'course_id'   => $cert->course_id,
        'issued_date' => $cert->issued_date->format('Y-m-d'),
    ]);
});
Route::get('/certificates/verify/{code}', [CertificateController::class, 'verify'])->name('certificates.verify');

require __DIR__ . '/auth.php';

