<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('postgraduate_lecturers')) {
            DB::statement('CREATE TABLE postgraduate_lecturers (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, sinta_id VARCHAR(255) NOT NULL UNIQUE, name VARCHAR(255) NULL, institution VARCHAR(255) NULL, study_program VARCHAR(255) NULL, profile_photo TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL)');
        }

        if (! Schema::hasTable('postgraduate_lecturer_details')) {
            DB::statement('CREATE TABLE postgraduate_lecturer_details (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, postgraduate_lecturer_id BIGINT UNSIGNED NOT NULL UNIQUE, sinta_id VARCHAR(255) NOT NULL UNIQUE, institution VARCHAR(255) NULL, study_program VARCHAR(255) NULL, profile_photo TEXT NULL, research_interests VARCHAR(255) NULL, sinta_score_overall INT NULL, sinta_score_3yr INT NULL, affil_score INT NULL, affil_score_3yr INT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL)');
        }

        if (! Schema::hasTable('postgraduate_lecturer_study_programs')) {
            DB::statement('CREATE TABLE postgraduate_lecturer_study_programs (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, postgraduate_lecturer_id BIGINT UNSIGNED NOT NULL, study_program_id BIGINT UNSIGNED NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY postgrad_lecturer_program_unique (postgraduate_lecturer_id, study_program_id))');
        }
    }

    public function down(): void
    {
    }
};
