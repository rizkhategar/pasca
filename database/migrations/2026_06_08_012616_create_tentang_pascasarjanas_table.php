<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_postgraduate_programs', function (Blueprint $table) {
            $table->id();
            $table->string('subheading')->default('Tentang Kami');
            $table->string('heading');
            $table->text('description');
            $table->json('points')->nullable(); // Disimpan sebagai JSON untuk repeater
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_postgraduate_programs');
    }
};