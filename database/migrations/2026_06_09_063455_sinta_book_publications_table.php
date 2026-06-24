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
        Schema::create('sinta_book_publications', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel sinta_lecturers yang baru
            $table->string('sinta_id');

            // Kolom yang ditranslasi ke bahasa Inggris
            $table->text('title'); // menggantikan judul
            $table->string('year')->nullable(); // menggantikan tahun
            $table->string('publisher')->nullable(); // menggantikan penerbit

            // Kolom standar untuk data buku SINTA
            $table->string('isbn')->nullable();
            $table->text('authors')->nullable();

            $table->timestamps();

            // Menambahkan foreign key constraint ke tabel sinta_lecturers
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
        Schema::dropIfExists('sinta_book_publications');
    }
};
