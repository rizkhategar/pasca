<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postgraduate_lecturer', function (Blueprint $table) {
            $table->id();
            $table->string('sinta_id')->unique();
            $table->string('name')->nullable();
            $table->string('institution')->nullable();
            $table->string('profile_photo')->nullable();
            $table->timestamps();

            $table->foreign('sinta_id')
                ->references('sinta_id')
                ->on('sinta_lecturers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postgraduate_lecturer');
    }
};
