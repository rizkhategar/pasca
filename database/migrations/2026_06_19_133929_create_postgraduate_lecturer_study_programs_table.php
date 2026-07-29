<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecturer_study_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('postgraduate_lecturer_id');
            $table->unsignedBigInteger('study_program_id');
            $table->timestamps();

            $table->foreign('postgraduate_lecturer_id', 'lecturer_study_programs_lecturer_fk')
                ->references('id')
                ->on('lecturers')
                ->cascadeOnDelete();

            $table->foreign('study_program_id', 'lecturer_study_programs_study_program_fk')
                ->references('id')
                ->on('study_programs')
                ->cascadeOnDelete();

            $table->unique(
                ['postgraduate_lecturer_id', 'study_program_id'],
                'lecturer_study_programs_lecturer_program_unique'
            );
            $table->index('study_program_id', 'lecturer_study_programs_study_program_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturer_study_programs');
    }
};
