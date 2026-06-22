<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departement', function (Blueprint $table) {
            $table->id();
            $table->string('sinta_id');
            // Menggunakan unsignedBigInteger karena ID dari API berformat angka/integer
            $table->unsignedBigInteger('id_departement'); 
            $table->timestamps();

            // Set Foreign Key ke tabel pasca_lecturers agar jika dosen dihapus, pivotnya ikut terhapus
            $table->foreign('sinta_id')
                  ->references('sinta_id')
                  ->on('pasca_lecturers')
                  ->onDelete('cascade');

            // Indexing agar proses query filter 'LIKE' yang lama berubah menjadi pencarian indeks cepat (High Performance)
            $table->index(['sinta_id', 'id_departement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departement');
    }
};