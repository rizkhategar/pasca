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
        Schema::create('sinta_lecturer_details', function (Blueprint $table) {
            // Menggunakan sinta_id sebagai Primary Key
            $table->string('sinta_id')->primary();
            
            // Kolom yang ditranslasi ke bahasa Inggris
            $table->string('name')->nullable();
            $table->string('institution')->nullable();
            $table->string('study_program')->nullable();
            $table->string('research_interests')->nullable(); // menggantikan bidang_minat
            
            // Kolom yang sudah berbahasa Inggris
            $table->text('profile_photo')->nullable();
            $table->integer('sinta_score_overall')->nullable();
            $table->integer('sinta_score_3yr')->nullable();
            $table->integer('affil_score')->nullable();
            $table->integer('affil_score_3yr')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sinta_lecturer_details');
    }
};