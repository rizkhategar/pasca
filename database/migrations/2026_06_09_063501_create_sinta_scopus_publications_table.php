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
        if (Schema::hasTable('sinta_scopus_publications')) {
            return;
        }

        Schema::create('sinta_scopus_publications', function (Blueprint $table) {
            $table->id();
            $table->string('sinta_id');
            $table->string('title');
            $table->string('year')->nullable();
            $table->text('article_url')->nullable();
            $table->text('journal_url')->nullable();
            $table->integer('citation')->nullable();
            $table->string('quartile')->nullable();
            $table->string('journal')->nullable();
            $table->string('author_order')->nullable();
            $table->string('creator')->nullable();
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
        Schema::dropIfExists('sinta_scopus_publications');
    }
};
