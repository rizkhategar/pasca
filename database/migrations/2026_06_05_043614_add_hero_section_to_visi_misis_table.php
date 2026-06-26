<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vision_mission')) {
            return;
        }

        Schema::table('vision_mission', function (Blueprint $table): void {
            if (! Schema::hasColumn('vision_mission', 'hero_title')) {
                $table->string('hero_title')->default('Visi & Misi')->nullable();
            }

            if (! Schema::hasColumn('vision_mission', 'hero_subtitle')) {
                $table->string('hero_subtitle')->default('Pascasarjana Universitas Ngudi Waluyo')->nullable();
            }

            if (! Schema::hasColumn('vision_mission', 'hero_image')) {
                $table->string('hero_image')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('vision_mission')) {
            return;
        }

        Schema::table('vision_mission', function (Blueprint $table): void {
            $columns = array_filter([
                Schema::hasColumn('vision_mission', 'hero_title') ? 'hero_title' : null,
                Schema::hasColumn('vision_mission', 'hero_subtitle') ? 'hero_subtitle' : null,
                Schema::hasColumn('vision_mission', 'hero_image') ? 'hero_image' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
