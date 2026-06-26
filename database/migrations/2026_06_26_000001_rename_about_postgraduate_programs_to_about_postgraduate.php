<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('about_postgraduate_programs') && ! Schema::hasTable('about_postgraduate')) {
            Schema::rename('about_postgraduate_programs', 'about_postgraduate');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('about_postgraduate') && ! Schema::hasTable('about_postgraduate_programs')) {
            Schema::rename('about_postgraduate', 'about_postgraduate_programs');
        }
    }
};
