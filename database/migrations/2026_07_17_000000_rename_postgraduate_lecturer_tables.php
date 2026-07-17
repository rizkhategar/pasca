<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('postgraduate_lecturers') && ! Schema::hasTable('lecturers')) {
            Schema::rename('postgraduate_lecturers', 'lecturers');
        }

        if (Schema::hasTable('postgraduate_lecturer_study_programs') && ! Schema::hasTable('lecturer_study_programs')) {
            Schema::rename('postgraduate_lecturer_study_programs', 'lecturer_study_programs');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lecturer_study_programs') && ! Schema::hasTable('postgraduate_lecturer_study_programs')) {
            Schema::rename('lecturer_study_programs', 'postgraduate_lecturer_study_programs');
        }

        if (Schema::hasTable('lecturers') && ! Schema::hasTable('postgraduate_lecturers')) {
            Schema::rename('lecturers', 'postgraduate_lecturers');
        }
    }
};
