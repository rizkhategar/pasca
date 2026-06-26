<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('about_postgraduate')) {
            return;
        }

        if (Schema::hasTable('about_postgraduate_programs')) {
            Schema::rename('about_postgraduate_programs', 'about_postgraduate');
            $this->syncColumns();

            return;
        }

        Schema::create('about_postgraduate', function (Blueprint $table) {
            $table->id();
            $table->string('hero_image')->nullable();
            $table->string('subheading')->default('Tentang Kami');
            $table->string('heading');
            $table->text('description');
            $table->json('points')->nullable();
            $table->string('direktur_image')->nullable();
            $table->string('direktur_name')->nullable();
            $table->string('direktur_title')->default('Direktur Pascasarjana Universitas Ngudi Waluyo')->nullable();
            $table->text('direktur_message')->nullable();
            $table->string('direktur_heading')->default('Sambutan Direktur')->nullable();
            $table->string('direktur_greeting')->default('Selamat Datang di Pascasarjana Universitas Ngudi Waluyo')->nullable();
            $table->timestamps();
        });
    }

    private function syncColumns(): void
    {
        Schema::table('about_postgraduate', function (Blueprint $table): void {
            if (! Schema::hasColumn('about_postgraduate', 'hero_image')) {
                $table->string('hero_image')->nullable()->after('id');
            }

            if (! Schema::hasColumn('about_postgraduate', 'direktur_image')) {
                $table->string('direktur_image')->nullable();
            }

            if (! Schema::hasColumn('about_postgraduate', 'direktur_name')) {
                $table->string('direktur_name')->nullable();
            }

            if (! Schema::hasColumn('about_postgraduate', 'direktur_title')) {
                $table->string('direktur_title')->default('Direktur Pascasarjana Universitas Ngudi Waluyo')->nullable();
            }

            if (! Schema::hasColumn('about_postgraduate', 'direktur_message')) {
                $table->text('direktur_message')->nullable();
            }

            if (! Schema::hasColumn('about_postgraduate', 'direktur_heading')) {
                $table->string('direktur_heading')->default('Sambutan Direktur')->nullable();
            }

            if (! Schema::hasColumn('about_postgraduate', 'direktur_greeting')) {
                $table->string('direktur_greeting')->default('Selamat Datang di Pascasarjana Universitas Ngudi Waluyo')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_postgraduate');
    }
};
