<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vision_mission', function (Blueprint $table) {
            $table->string('hero_title')->default('Visi & Misi')->nullable();
            $table->string('hero_subtitle')->default('Pascasarjana Universitas Ngudi Waluyo')->nullable();
            $table->string('hero_image')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vision_mission', function (Blueprint $table) {
            $table->dropColumn(['hero_title', 'hero_subtitle', 'hero_image']);
        });
    }
};
