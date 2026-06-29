<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('undergraduate_lecturers')) {
            Schema::create('undergraduate_lecturers', function (Blueprint $table) {
                $table->id();
                $table->string('sinta_id')->unique();
                $table->string('name')->nullable();
                $table->string('institution')->nullable();
                $table->string('study_program')->nullable();
                $table->text('profile_photo')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('undergraduate_lecturer_study_programs')) {
            Schema::create('undergraduate_lecturer_study_programs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('undergraduate_lecturer_id');
                $table->foreignId('study_program_id');
                $table->timestamps();

                $table->foreign('undergraduate_lecturer_id', 'ugl_sp_ugl_id_fk')
                    ->references('id')
                    ->on('undergraduate_lecturers')
                    ->cascadeOnDelete();

                $table->foreign('study_program_id', 'ugl_sp_sp_id_fk')
                    ->references('id')
                    ->on('study_programs')
                    ->cascadeOnDelete();

                $table->unique(['undergraduate_lecturer_id', 'study_program_id'], 'undergrad_lecturer_program_unique');
            });
        }
    }

    public function down(): void
    {
    }
};
