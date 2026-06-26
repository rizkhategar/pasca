<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('about_postgraduate')) {
            return;
        }

        Schema::table('about_postgraduate', function (Blueprint $table): void {
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
        if (! Schema::hasTable('about_postgraduate')) {
            return;
        }

        Schema::table('about_postgraduate', function (Blueprint $table): void {
            $columns = array_filter([
                Schema::hasColumn('about_postgraduate', 'direktur_heading') ? 'direktur_heading' : null,
                Schema::hasColumn('about_postgraduate', 'direktur_greeting') ? 'direktur_greeting' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
