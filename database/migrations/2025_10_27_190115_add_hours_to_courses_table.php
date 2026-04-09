<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('courses', function (Blueprint $table) {
            // usá el tipo/valor que prefieras; unsignedSmallInteger es suficiente hasta 65535
            $table->unsignedSmallInteger('hours')->nullable()->after('description');
        });
    }
    public function down(): void {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('hours');
        });
    }
};
