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
        Schema::create('sinta_lecturers', function (Blueprint $table) {
            // ID numerik utama Laravel/Filament.
            $table->id();

            // SINTA ID tetap unik dan dipakai sebagai identifier relasi aplikasi.
            $table->string('sinta_id')->unique();

            // Kolom yang ditranslasi ke Bahasa Inggris
            $table->string('name');
            $table->string('department')->nullable();

            // Kolom yang sudah berbahasa Inggris
            $table->string('scopus_h_index')->nullable();
            $table->string('google_scholar_h_index')->nullable();
            $table->string('sinta_score_3yr')->nullable();
            $table->string('sinta_score')->nullable();
            $table->string('affiliation_score_3yr')->nullable();
            $table->string('affiliation_score')->nullable();
            $table->text('profile_url')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sinta_lecturers');
    }
};
