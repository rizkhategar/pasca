<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('sinta_scholar_publications')) {
            return;
        }

        Schema::create('sinta_scholar_publications', function (Blueprint $table) {
            $table->id();
            $table->string('sinta_id');
            $table->string('title');
            $table->text('scholar_url')->nullable();
            $table->string('year')->nullable();
            $table->text('authors')->nullable();
            $table->string('source')->nullable();
            $table->integer('citation')->nullable();
            $table->timestamps();

            $table->foreign('sinta_id')
                  ->references('sinta_id')
                  ->on('sinta_lecturers')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sinta_scholar_publications');
    }
};
