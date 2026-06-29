<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('undergraduate_lecturers', function (Blueprint $table) {
            $table->id();
            $table->string('sinta_id')->unique();
            $table->string('name')->nullable();
            $table->string('institution')->nullable();
            $table->string('profile_photo')->nullable();
            $table->timestamps();

            $table->foreign('sinta_id', 'undergraduate_lecturers_sinta_id_fk')
                ->references('sinta_id')
                ->on('sinta_lecturers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('undergraduate_lecturers');
    }
};
