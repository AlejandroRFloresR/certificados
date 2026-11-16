<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Los certificados emitidos sobreviven al borrado de su usuario o su curso:
 * en lugar de borrarse en cascada, quedan con user_id / course_id en NULL.
 * Siguen descargándose y verificándose con los datos de snapshot_data.
 * (SQL directo: el proyecto no tiene doctrine/dbal para ->change())
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE certificates DROP FOREIGN KEY certificates_user_id_foreign');
        DB::statement('ALTER TABLE certificates DROP FOREIGN KEY certificates_course_id_foreign');

        DB::statement('ALTER TABLE certificates MODIFY user_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE certificates MODIFY course_id BIGINT UNSIGNED NULL');

        DB::statement('ALTER TABLE certificates ADD CONSTRAINT certificates_user_id_foreign
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        DB::statement('ALTER TABLE certificates ADD CONSTRAINT certificates_course_id_foreign
            FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        // Volver atrás exigiría borrar los certificados de usuarios o cursos eliminados
        $orphans = DB::table('certificates')->whereNull('user_id')->orWhereNull('course_id')->count();
        if ($orphans > 0) {
            throw new RuntimeException("Hay {$orphans} certificados de usuarios o cursos eliminados: no se puede revertir sin perderlos.");
        }

        DB::statement('ALTER TABLE certificates DROP FOREIGN KEY certificates_user_id_foreign');
        DB::statement('ALTER TABLE certificates DROP FOREIGN KEY certificates_course_id_foreign');

        DB::statement('ALTER TABLE certificates MODIFY user_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE certificates MODIFY course_id BIGINT UNSIGNED NOT NULL');

        DB::statement('ALTER TABLE certificates ADD CONSTRAINT certificates_user_id_foreign
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE certificates ADD CONSTRAINT certificates_course_id_foreign
            FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE');
    }
};
