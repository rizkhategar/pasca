<?php

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('study_programs')) {
            return;
        }
    }

    public function down(): void
    {
    }
};
