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
        Schema::table('sinta_lecturer_details', function (Blueprint $table) {
            // Menambahkan kolom department setelah research_interests
            $table->string('department')->nullable()->after('research_interests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sinta_lecturer_details', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }
};