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
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('about_postgraduate')) {
            return;
        }

        Schema::table('about_postgraduate', function (Blueprint $table): void {
            $columns = array_filter([
                Schema::hasColumn('about_postgraduate', 'direktur_image') ? 'direktur_image' : null,
                Schema::hasColumn('about_postgraduate', 'direktur_name') ? 'direktur_name' : null,
                Schema::hasColumn('about_postgraduate', 'direktur_title') ? 'direktur_title' : null,
                Schema::hasColumn('about_postgraduate', 'direktur_message') ? 'direktur_message' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
