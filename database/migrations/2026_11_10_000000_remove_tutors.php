<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Elimina los tutores del sistema.
 *
 * - Los usuarios con rol "tutor" pasan a rol "user" (conservan su cuenta, cursos y certificados).
 * - Se borra el rol "tutor" y las tablas tutors y course_tutor.
 * - NO se tocan los certificados ni los archivos de storage/app/public/signatures:
 *   los certificados emitidos guardaron sus firmas en snapshot_data y las siguen mostrando.
 *
 * Ojo: el down() recrea las tablas vacías. Los datos de tutores y sus asignaciones
 * solo se recuperan desde un backup de la base.
 */
return new class extends Migration {
    public function up(): void
    {
        $tutorRole = Role::where('name', 'tutor')->where('guard_name', 'web')->first();

        if ($tutorRole) {
            $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

            // Usuarios cuyo único rol era "tutor": pasan a "user" para que puedan seguir entrando
            $tutorUserIds = DB::table('model_has_roles')
                ->where('role_id', $tutorRole->id)
                ->where('model_type', 'App\\Models\\User')
                ->pluck('model_id');

            foreach ($tutorUserIds as $userId) {
                $hasOtherRole = DB::table('model_has_roles')
                    ->where('model_type', 'App\\Models\\User')
                    ->where('model_id', $userId)
                    ->where('role_id', '!=', $tutorRole->id)
                    ->exists();

                if (!$hasOtherRole) {
                    DB::table('model_has_roles')->insert([
                        'role_id'    => $userRole->id,
                        'model_type' => 'App\\Models\\User',
                        'model_id'   => $userId,
                    ]);
                }
            }

            // Borra el rol y sus asignaciones
            $tutorRole->delete();
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        Schema::dropIfExists('course_tutor');
        Schema::dropIfExists('tutors');
    }

    public function down(): void
    {
        Role::firstOrCreate(['name' => 'tutor', 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (!Schema::hasTable('tutors')) {
            Schema::create('tutors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->unique()->constrained('users')->onDelete('cascade');
                $table->string('name');
                $table->string('signature')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('course_tutor')) {
            Schema::create('course_tutor', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained()->onDelete('cascade');
                $table->foreignId('tutor_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            });
        }
    }
};
