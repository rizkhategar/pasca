<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vision_mission', function (Blueprint $table) {
            $table->string('judul_tujuan')->default('Tujuan')->nullable();
            $table->text('tujuan')->nullable();
            
            $table->string('judul_tujuan_bidang')->default('Tujuan UNW Dalam Bidang')->nullable();
            $table->text('tujuan_bidang')->nullable();
            
            $table->string('judul_sasaran_target')->default('Sasaran dan Target')->nullable();
            $table->text('sasaran_target')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vision_mission', function (Blueprint $table) {
            $table->dropColumn([
                'judul_tujuan', 'tujuan', 
                'judul_tujuan_bidang', 'tujuan_bidang', 
                'judul_sasaran_target', 'sasaran_target'
            ]);
        });
    }
};
