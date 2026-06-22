<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasca_lecturers', function (Blueprint $table) {
            // SINTA ID sebagai Primary Key berbentuk String
            $table->string('sinta_id')->primary();
            $table->string('name')->nullable();
            $table->string('institution')->nullable();
            $table->string('study_program')->nullable();
            $table->string('profile_photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasca_lecturers');
    }
};