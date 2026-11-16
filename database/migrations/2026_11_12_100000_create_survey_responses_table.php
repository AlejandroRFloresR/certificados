<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Envío del certificado por correo + encuesta de satisfacción.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            // Link único de la encuesta (no se usa el código del certificado: ese es público por el QR)
            $table->string('survey_token', 64)->nullable()->unique()->after('certificate_code');
            $table->timestamp('emailed_at')->nullable()->after('survey_token');
            $table->string('email_error')->nullable()->after('emailed_at');
        });

        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->unique()->constrained()->cascadeOnDelete();
            // Se guarda aparte para que los resultados del curso no dependan del certificado
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('overall');       // 1 a 5
            $table->unsignedTinyInteger('content');
            $table->unsignedTinyInteger('teachers');
            $table->unsignedTinyInteger('organization');
            $table->boolean('recommend');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropUnique(['survey_token']);
            $table->dropColumn(['survey_token', 'emailed_at', 'email_error']);
        });
    }
};
