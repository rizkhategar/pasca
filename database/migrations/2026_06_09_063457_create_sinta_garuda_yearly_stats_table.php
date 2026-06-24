<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinta_garuda_yearly_stats', function (Blueprint $table) {
            $table->id();
            $table->string('sinta_id');
            $table->string('year');
            $table->integer('articles')->default(0);
            $table->timestamps();

            $table->foreign('sinta_id')
                ->references('sinta_id')
                ->on('sinta_lecturers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinta_garuda_yearly_stats');
    }
};
