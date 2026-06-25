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
        Schema::create('sinta_scholar_yearly_stats', function (Blueprint $table) {
            $table->id();
            $table->string('sinta_id');

            // Kolom yang ditranslasi ke bahasa Inggris
            $table->string('year'); // menggantikan tahun

            // Kolom yang sudah berbahasa Inggris
            $table->integer('publications')->default(0);
            $table->integer('citations')->default(0);
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
        Schema::dropIfExists('sinta_scholar_yearly_stats');
    }
};
