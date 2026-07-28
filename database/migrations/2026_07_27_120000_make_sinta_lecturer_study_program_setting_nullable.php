<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sinta_lecturer_study_program_settings', function (Blueprint $table): void {
            $table->foreignId('study_program_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('sinta_lecturer_study_program_settings')
            ->whereNull('study_program_id')
            ->delete();

        Schema::table('sinta_lecturer_study_program_settings', function (Blueprint $table): void {
            $table->foreignId('study_program_id')->nullable(false)->change();
        });
    }
};
