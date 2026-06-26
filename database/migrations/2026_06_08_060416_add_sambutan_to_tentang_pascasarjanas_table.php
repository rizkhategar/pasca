<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_postgraduate', function (Blueprint $table) {
            $table->string('direktur_image')->nullable();
            $table->string('direktur_name')->nullable();
            $table->string('direktur_title')->default('Direktur Pascasarjana Universitas Ngudi Waluyo')->nullable();
            $table->text('direktur_message')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('about_postgraduate', function (Blueprint $table) {
            $table->dropColumn(['direktur_image', 'direktur_name', 'direktur_title', 'direktur_message']);
        });
    }
};
