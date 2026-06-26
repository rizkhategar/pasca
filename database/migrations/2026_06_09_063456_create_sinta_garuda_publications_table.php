<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sinta_garuda_publications')) {
            return;
        }

        Schema::create('sinta_garuda_publications', function (Blueprint $table) {
            $table->id();
            $table->string('sinta_id');
            $table->text('title');
            $table->text('article_url')->nullable();
            $table->text('journal_url')->nullable();
            $table->string('year')->nullable();
            $table->string('publisher')->nullable();
            $table->string('journal')->nullable();
            $table->string('author_order')->nullable();
            $table->text('authors')->nullable();
            $table->string('doi')->nullable();
            $table->string('accreditation')->nullable();
            $table->timestamps();

            $table->foreign('sinta_id')
                ->references('sinta_id')
                ->on('sinta_lecturers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinta_garuda_publications');
    }
};
