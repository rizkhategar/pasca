<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('about_postgraduate')) {
            return;
        }

        if (Schema::hasColumn('about_postgraduate', 'hero_image')) {
            return;
        }

        Schema::table('about_postgraduate', function (Blueprint $table): void {
            $table->string('hero_image')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('about_postgraduate')) {
            return;
        }

        if (! Schema::hasColumn('about_postgraduate', 'hero_image')) {
            return;
        }

        Schema::table('about_postgraduate', function (Blueprint $table): void {
            $table->dropColumn('hero_image');
        });
    }
};
