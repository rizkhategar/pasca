<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vision_mission', function (Blueprint $table) {
            $table->string('judul_visi')->default('Visi')->after('id');
            $table->string('judul_misi')->default('Misi')->after('visi');
        });
    }

    public function down(): void
    {
        Schema::table('vision_mission', function (Blueprint $table) {
            $table->dropColumn(['judul_visi', 'judul_misi']);
        });
    }
};
