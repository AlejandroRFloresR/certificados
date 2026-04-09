<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TutorController;
use App\Models\User;
Use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [CertificateController::class, 'lookup'])->middleware('throttle:10,1')->name('home');
Route::get('/certificates/download/{code}', [CertificateController::class, 'downloadByCode'])
    ->name('certificates.download');

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
        Route::get('/courses/{course}/users/edit', [\App\Http\Controllers\AdminController::class, 'editCourseUsers'])
            ->name('courses.users.edit');
        Route::put('/courses/{course}/users', [\App\Http\Controllers\AdminController::class, 'updateCourseUsers'])
            ->name('courses.users.update');
        Route::get('/courses/{course}/users/export', [AdminController::class, 'exportCourseUsers'])
            ->name('courses.users.export');
        Route::get('/courses/{course}/tutors/edit', [\App\Http\Controllers\AdminController::class, 'editCourseTutors'])
        ->name('courses.tutors.edit');
        Route::put('/courses/{course}/tutors', [\App\Http\Controllers\AdminController::class, 'updateCourseTutors'])
        ->name('courses.tutors.update');

        Route::get('/tutors/{tutor}/signature', [TutorController::class, 'editSignature'])
                ->name('tutors.signature.edit');
        Route::put('/tutors/{tutor}/signature', [TutorController::class, 'updateSignature'])
                ->name('tutors.signature.update');

        // Panel/AdminController (evitamos duplicar misma URL y name)
        Route::get('/courses', [AdminController::class, 'courses'])->name('courses');
        Route::get('/courses/form', [AdminController::class, 'showForm'])->name('courses.form');
        Route::get('/courses/{course}/users', [AdminController::class, 'courseUsers'])->name('courses.users');

        // Usuarios (panel admin)
        Route::resource('users', AdminUserController::class)->except(['show']);
        Route::get('users/import', [\App\Http\Controllers\AdminUserImportController::class, 'create'])
            ->name('users.import.create');
        Route::post('users/import', [\App\Http\Controllers\AdminUserImportController::class, 'store'])
            ->name('users.import.store');
        // Acciones extra coherentes con kebab-case
        Route::post('users/{user}/assign-role', [AdminUserController::class, 'assignRole'])->name('users.assign-role');
        Route::get('users/{user}/password', [AdminUserController::class, 'editPassword'])->name('users.edit-password');
        Route::put('users/{user}/password', [AdminUserController::class, 'updatePassword'])->name('users.update-password');
    });
  
    Route::middleware(['auth','role:tutor'])->group(function () {
    Route::get('/my/signature', [TutorController::class, 'editMySignature'])
        ->name('tutors.me.signature.edit');
    Route::put('/my/signature', [TutorController::class, 'updateMySignature'])
        ->name('tutors.me.signature.update');
    });
/**
 * Cursos (público / general)
 */
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');

/**
 * Dashboard
 */
Route::get('/dashboard', function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    // 1) Cursos donde el usuario está inscripto (rol alumno)
    $studentCourses = $user->courses()
        ->orderBy('start_date', 'desc')
        ->get();

    // 2) Certificados del usuario, indexados por course_id
    $certsByCourse = $user->certificates()
        ->select('id','course_id','certificate_code','type','issued_date','snapshot_data')
        ->get()
        ->keyBy('course_id');  // => [course_id => Certificate]

    // 3) Cursos donde es tutor (si tiene rol tutor y tutor asociado)
    $tutorCourses = collect();
    if ($user->hasRole('tutor') && $user->tutor) {
        $tutorCourses = $user->tutor
            ->courses()
            ->orderBy('start_date', 'desc')
            ->get();
    }

    return view('dashboard', compact('studentCourses','certsByCourse','tutorCourses'));
})->middleware(['auth', 'verified'])->name('dashboard');

/**
 * Rutas autenticadas (usuarios logueados)
 */
Route::middleware('auth')->group(function () {
    Route::get('/courses/{course}/certificate/{user}/{type?}', [CertificateController::class,'generate'])
    ->whereIn('type', ['asistio','dicto','aprobado'])
    ->name('courses.certificate');
    Route::post('/certificates/emit', [CertificateController::class, 'emit'])
    ->middleware(['auth','role:admin'])
    ->name('certificates.emit');

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

