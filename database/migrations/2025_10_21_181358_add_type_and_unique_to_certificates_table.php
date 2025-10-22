<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('certificates', function (Blueprint $table) {
            // Tipo de certificado
            // Si usas MySQL 8 puedes usar enum; con versiones anteriores usa string y valida en app.
            $table->string('type', 20)->nullable()->after('course_id'); // luego lo haremos required en app

            // Asegurar UN certificado por (user, course)
            // Si ya existe un índice similar, elimínalo antes.
            $table->unique(['user_id', 'course_id'], 'uniq_user_course_certificate');
        });
    }

    public function down(): void {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropUnique('uniq_user_course_certificate');
            $table->dropColumn('type');
        });
    }
};
