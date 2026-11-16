<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pasa la categoría de los cursos de texto libre a una tabla propia.
 * Las categorías escritas igual salvo mayúsculas o tildes ("Enfermería" / "enfermeria")
 * quedan unificadas en una sola (la intercalación utf8mb4_unicode_ci las considera iguales).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('is_public')
                ->constrained('categories')->nullOnDelete();
        });

        // Copiar cada texto distinto a la tabla y vincular los cursos
        $names = DB::table('courses')->whereNotNull('category')->pluck('category')
            ->map(fn ($n) => trim(preg_replace('/\s+/', ' ', $n)))
            ->filter();

        foreach ($names as $name) {
            $id = DB::table('categories')->where('name', $name)->value('id');

            if (!$id) {
                $id = DB::table('categories')->insertGetId([
                    'name' => $name, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            DB::table('courses')->where('category', $name)->update(['category_id' => $id]);
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('is_public');
        });

        DB::table('courses')
            ->join('categories', 'categories.id', '=', 'courses.category_id')
            ->update(['courses.category' => DB::raw('categories.name')]);

        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::dropIfExists('categories');
    }
};
