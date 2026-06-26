<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_postgraduate', function (Blueprint $table) {
            $table->id();
            $table->string('hero_image')->nullable();
            $table->string('subheading')->default('Tentang Kami');
            $table->string('heading');
            $table->text('description');
            $table->json('points')->nullable();
            $table->string('direktur_image')->nullable();
            $table->string('direktur_name')->nullable();
            $table->string('direktur_title')->default('Direktur Pascasarjana Universitas Ngudi Waluyo')->nullable();
            $table->text('direktur_message')->nullable();
            $table->string('direktur_heading')->default('Sambutan Direktur')->nullable();
            $table->string('direktur_greeting')->default('Selamat Datang di Pascasarjana Universitas Ngudi Waluyo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_postgraduate');
    }
};
