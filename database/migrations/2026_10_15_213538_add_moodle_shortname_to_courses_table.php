<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // "Nombre corto" del curso en Moodle, usado para inscribir al subir usuarios
            $table->string('moodle_shortname', 100)->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('moodle_shortname');
        });
    }
};
