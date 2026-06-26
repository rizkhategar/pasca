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
            $table->unsignedBigInteger('postgraduate_lecturer_id');
            $table->unsignedBigInteger('id_study_program');
            $table->timestamps();

            $table->foreign('postgraduate_lecturer_id', 'plsp_lecturer_fk')
                ->references('id')
                ->on('postgraduate_lecturer')
                ->cascadeOnDelete();

            $table->foreign('id_study_program', 'plsp_study_program_fk')
                ->references('id_unw_program_studi')
                ->on('study_program')
                ->cascadeOnDelete();

            $table->unique(
                ['postgraduate_lecturer_id', 'id_study_program'],
                'plsp_lecturer_program_unique'
            );
            $table->index('id_study_program', 'plsp_study_program_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postgraduate_lecturer_study_program');
    }
};
