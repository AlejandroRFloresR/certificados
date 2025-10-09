<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tutors', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->after('signature');
            $table->unique('user_id'); // 1:1
        });
    }

    public function down(): void {
        Schema::table('tutors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};

