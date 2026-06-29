<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('undergraduate_lecturer_study_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('undergraduate_lecturer_id');
            $table->unsignedBigInteger('study_program_id');
            $table->timestamps();

            $table->foreign('undergraduate_lecturer_id', 'ulsp_lecturer_fk')
                ->references('id')
                ->on('undergraduate_lecturers')
                ->cascadeOnDelete();

            $table->foreign('study_program_id', 'ulsp_study_program_fk')
                ->references('id')
                ->on('study_programs')
                ->cascadeOnDelete();

            $table->unique(
                ['undergraduate_lecturer_id', 'study_program_id'],
                'ulsp_lecturer_program_unique'
            );
            $table->index('study_program_id', 'ulsp_study_program_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('undergraduate_lecturer_study_programs');
    }
};
