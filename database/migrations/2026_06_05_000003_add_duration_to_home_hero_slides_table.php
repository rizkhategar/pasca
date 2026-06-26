<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sliders')) {
            return;
        }

        if (Schema::hasColumn('sliders', 'duration_ms')) {
            return;
        }

        Schema::table('sliders', function (Blueprint $table): void {
            $table->unsignedInteger('duration_ms')->default(3000)->after('sort_order');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sliders')) {
            return;
        }

        if (! Schema::hasColumn('sliders', 'duration_ms')) {
            return;
        }

        Schema::table('sliders', function (Blueprint $table): void {
            $table->dropColumn('duration_ms');
        });
    }
};
