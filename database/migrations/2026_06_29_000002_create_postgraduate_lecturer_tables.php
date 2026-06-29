<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('postgraduate_lecturers')) {
            Schema::create('postgraduate_lecturers', function (Blueprint $table) {
                $table->id();
                $table->string('sinta_id')->unique();
                $table->string('name')->nullable();
                $table->string('institution')->nullable();
                $table->string('study_program')->nullable();
                $table->text('profile_photo')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('postgraduate_lecturer_study_programs')) {
            Schema::create('postgraduate_lecturer_study_programs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('postgraduate_lecturer_id');
                $table->foreignId('study_program_id');
                $table->timestamps();

                $table->foreign('postgraduate_lecturer_id', 'pgl_sp_pgl_id_fk')
                    ->references('id')
                    ->on('postgraduate_lecturers')
                    ->cascadeOnDelete();

                $table->foreign('study_program_id', 'pgl_sp_sp_id_fk')
                    ->references('id')
                    ->on('study_programs')
                    ->cascadeOnDelete();

                $table->unique(['postgraduate_lecturer_id', 'study_program_id'], 'postgrad_lecturer_program_unique');
            });
        }
    }

    public function down(): void
    {
    }
};
