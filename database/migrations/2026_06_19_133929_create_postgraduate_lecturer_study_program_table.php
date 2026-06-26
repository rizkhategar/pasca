<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postgraduate_lecturer_study_program', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postgraduate_lecturer_id')
                ->constrained('postgraduate_lecturer')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('id_study_program');
            $table->timestamps();

            $table->foreign('id_study_program')
                ->references('id_unw_program_studi')
                ->on('study_program')
                ->cascadeOnDelete();

            $table->unique(
                ['postgraduate_lecturer_id', 'id_study_program'],
                'postgraduate_lecturer_program_unique'
            );
            $table->index('id_study_program');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postgraduate_lecturer_study_program');
    }
};
