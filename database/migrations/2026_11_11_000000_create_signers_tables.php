<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Firmantes de los certificados (director, coordinadores…) y qué firmantes lleva cada curso.
 * Las imágenes viven en storage/app/public/signatures y NUNCA se borran: los certificados
 * emitidos guardan la ruta de la firma que tenían al emitirse.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('signers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position')->nullable();   // cargo, se imprime debajo del nombre
            $table->string('signature');               // ruta en el disco public
            $table->timestamps();
        });

        Schema::create('course_signer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('signer_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(0); // orden de impresión en el certificado
            $table->unique(['course_id', 'signer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_signer');
        Schema::dropIfExists('signers');
    }
};
