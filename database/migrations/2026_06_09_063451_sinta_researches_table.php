<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sinta_researches', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel sinta_lecturers yang baru
            $table->string('sinta_id');

            // Kolom yang ditranslasi ke bahasa Inggris
            $table->text('title'); // menggantikan judul
            $table->string('scheme')->nullable(); // menggantikan skema
            $table->text('personnel')->nullable(); // menggantikan personils
            $table->string('year')->nullable(); // menggantikan tahun
            $table->string('funding')->nullable(); // menggantikan dana

            // Kolom yang sudah berbahasa Inggris
            $table->string('leader')->nullable();
            $table->string('status')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();

            // Foreign key constraint ke sinta_lecturers
            $table->foreign('sinta_id')
                  ->references('sinta_id')
                  ->on('sinta_lecturers')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sinta_researches');
    }
};
