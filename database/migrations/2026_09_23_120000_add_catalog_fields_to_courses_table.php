<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Permite ->change() sin doctrine/dbal (Laravel 10)
        Schema::useNativeSchemaOperationsIfPossible();

        Schema::table('courses', function (Blueprint $table) {
            // Solo los cursos marcados como públicos aparecen en el catálogo /cursos
            $table->boolean('is_public')->default(false)->after('hours');
            $table->string('category', 100)->nullable()->after('is_public');
            $table->string('modality', 20)->nullable()->after('category'); // presencial | virtual | hibrido
            $table->string('location')->nullable()->after('modality');

            // El formulario de alta no siempre envía fechas
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
        });
    }

    public function down(): void {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'category', 'modality', 'location']);
        });
    }
};
