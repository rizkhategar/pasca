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
        if (Schema::hasColumn('sinta_lecturer_details', 'name')) {
            Schema::table('sinta_lecturer_details', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('sinta_lecturer_details', 'name')) {
            Schema::table('sinta_lecturer_details', function (Blueprint $table) {
                $table->string('name')->nullable()->after('sinta_id');
            });
        }
    }
};
