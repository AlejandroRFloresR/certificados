<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PublicCourseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MoodleController;
use App\Http\Controllers\SignerController;
use App\Http\Controllers\SurveyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [CertificateController::class, 'lookup'])->middleware('throttle:10,1')->name('home');
Route::get('/certificates/download/{code}', [CertificateController::class, 'downloadByCode'])
    ->name('certificates.download');

// Encuesta de satisfacción: link único que llega por correo (sin iniciar sesión)
Route::get('/encuesta/{token}', [SurveyController::class, 'show'])->name('survey.show');
Route::post('/encuesta/{token}', [SurveyController::class, 'store'])->middleware('throttle:10,1')->name('survey.store');

// Catálogo público de cursos (React)
Route::get('/cursos', [PublicCourseController::class, 'index'])->name('catalog.index');

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
        // Ficha del curso (pestaña Alumnos) y emisión masiva de certificados
        Route::get('/courses/{course}/users', [AdminController::class, 'courseUsers'])->name('courses.users');
        Route::post('/courses/{course}/certificates', [AdminController::class, 'issueCertificates'])->name('courses.certificates.issue');
        Route::post('/certificates/{certificate}/email', [AdminController::class, 'resendCertificate'])->name('certificates.email');
        Route::get('/certificates/{certificate}/email', [AdminController::class, 'previewCertificateEmail'])->name('certificates.email.preview');
        Route::get('/courses/{course}/survey', [SurveyController::class, 'results'])->name('courses.survey');

        // Firmantes de los certificados
        Route::resource('signers', SignerController::class)->except(['show']);
        // Subir archivos con PUT no funciona en PHP: el formulario de edición envía POST con _method=put
        // y Laravel lo resuelve a la ruta "update" de arriba

        // Usuarios (panel admin)
        Route::resource('users', AdminUserController::class)->except(['show']);
        Route::get('users/import', [\App\Http\Controllers\AdminUserImportController::class, 'create'])
            ->name('users.import.create');
        Route::post('users/import', [\App\Http\Controllers\AdminUserImportController::class, 'store'])
            ->name('users.import.store');
        // Acciones extra coherentes con kebab-case
        Route::post('users/{user}/assign-role', [AdminUserController::class, 'assignRole'])->name('users.assign-role');

        // Moodle: exportar alumnos e importar aprobados
        Route::get('/courses/{course}/moodle/export', [MoodleController::class, 'export'])->name('courses.moodle.export');
        Route::get('/courses/{course}/moodle', [MoodleController::class, 'create'])->name('courses.moodle');
        Route::post('/courses/{course}/moodle/preview', [MoodleController::class, 'preview'])->name('courses.moodle.preview');
        Route::delete('/courses/{course}/moodle/preview', [MoodleController::class, 'cancel'])->name('courses.moodle.cancel');
        Route::post('/courses/{course}/moodle/confirm', [MoodleController::class, 'confirm'])->name('courses.moodle.confirm');

    });

/**
 * Cursos (público / general)
 */
Route::get('/courses', [CourseController::class, 'index'])->middleware('auth')->name('courses.index');

/**
 * Dashboard
 */
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth'])
    ->name('dashboard');

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

    // FIX: se quitó la "}" extra en la URL
    Route::post('courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
});

require __DIR__ . '/auth.php';

