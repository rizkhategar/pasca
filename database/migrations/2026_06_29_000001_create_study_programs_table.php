<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('study_programs')) {
            return;
        }

        DB::statement('CREATE TABLE study_programs (id BIGINT UNSIGNED NOT NULL PRIMARY KEY, name VARCHAR(255) NOT NULL, jenjang VARCHAR(255) NULL, faculty_name VARCHAR(255) NULL, raw_payload JSON NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL)');
        DB::statement('CREATE INDEX study_programs_jenjang_index ON study_programs (jenjang)');
    }

    public function down(): void
    {
    }
};
