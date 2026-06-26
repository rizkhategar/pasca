<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vision_missions') || Schema::hasTable('vision_mission')) {
            return;
        }

        Schema::create('vision_missions', function (Blueprint $table) {
            $table->id();
            $table->string('vision_title')->default('Vision');
            $table->text('vision');
            $table->string('mission_title')->default('Mission');
            $table->text('mission');
            $table->string('objectives_title')->default('Objectives')->nullable();
            $table->text('objectives')->nullable();
            $table->string('field_objectives_title')->default('UNW Field Objectives')->nullable();
            $table->text('field_objectives')->nullable();
            $table->string('goals_targets_title')->default('Goals and Targets')->nullable();
            $table->text('goals_targets')->nullable();
            $table->string('hero_title')->default('Vision & Mission')->nullable();
            $table->string('hero_subtitle')->default('Postgraduate School Universitas Ngudi Waluyo')->nullable();
            $table->string('hero_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vision_missions');
    }
};
