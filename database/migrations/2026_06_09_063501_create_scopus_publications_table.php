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
        Schema::create('sinta_scopus_publications', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel sinta_lecturers yang baru
            $table->string('sinta_id'); 
            
            // Kolom yang ditranslasi ke bahasa Inggris
            $table->string('title'); // menggantikan judul
            $table->string('year')->nullable(); // menggantikan tahun
            $table->text('article_url')->nullable(); // menggantikan url_artikel
            $table->text('journal_url')->nullable(); // menggantikan url_journal
            
            // Kolom yang sudah berbahasa Inggris
            $table->integer('citation')->nullable();
            $table->string('quartile')->nullable();
            $table->string('journal')->nullable();
            $table->string('author_order')->nullable();
            $table->string('creator')->nullable();
            
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
        Schema::dropIfExists('sinta_scopus_publications');
    }
};