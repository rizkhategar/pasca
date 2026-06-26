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
        if (Schema::hasTable('sinta_book_publications')) {
            return;
        }

        Schema::create('sinta_book_publications', function (Blueprint $table) {
            $table->id();
            $table->string('sinta_id');
            $table->text('title');
            $table->string('year')->nullable();
            $table->string('publisher')->nullable();
            $table->string('isbn')->nullable();
            $table->text('authors')->nullable();
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
        Schema::dropIfExists('sinta_book_publications');
    }
};
