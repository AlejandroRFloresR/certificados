<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tutors', function (Blueprint $table) {
            if (!Schema::hasColumn('tutors','user_id')) {
                $table->foreignId('user_id')
                    ->after('id')
                    ->constrained('users')
                    ->onDelete('cascade'); // borra tutor si se borra el user
                $table->unique('user_id'); // 1 tutor por usuario
            }
        });
    }
    public function down(): void {
        Schema::table('tutors', function (Blueprint $table) {
            if (Schema::hasColumn('tutors','user_id')) {
                $table->dropUnique(['user_id']);
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};