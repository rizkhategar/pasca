<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_postgraduate', function (Blueprint $table) {
            $table->string('direktur_heading')->default('Sambutan Direktur')->nullable();
            $table->string('direktur_greeting')->default('Selamat Datang di Pascasarjana Universitas Ngudi Waluyo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('about_postgraduate', function (Blueprint $table) {
            $table->dropColumn(['direktur_heading', 'direktur_greeting']);
        });
    }
};
