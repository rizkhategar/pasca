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
        Schema::create('sinta_service_yearly', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel sinta_lecturers yang baru
            $table->string('sinta_id');

            // Kolom yang ditranslasi ke bahasa Inggris
            $table->string('year'); // menggantikan tahun
            $table->integer('count')->default(0); // menggantikan jumlah

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
        Schema::dropIfExists('sinta_service_yearly');
    }
};
