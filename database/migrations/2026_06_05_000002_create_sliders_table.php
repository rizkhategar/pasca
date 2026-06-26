<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Pascasarjana Universitas Ngudi Waluyo');
            $table->string('subtitle')->default('Pascasarjana Universitas Ngudi Waluyo');
            $table->string('image_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('duration_ms')->default(3000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
