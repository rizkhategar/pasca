<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vision_mission')) {
            return;
        }

        Schema::table('vision_mission', function (Blueprint $table): void {
            if (! Schema::hasColumn('vision_mission', 'judul_tujuan')) {
                $table->string('judul_tujuan')->default('Tujuan')->nullable();
            }

            if (! Schema::hasColumn('vision_mission', 'tujuan')) {
                $table->text('tujuan')->nullable();
            }

            if (! Schema::hasColumn('vision_mission', 'judul_tujuan_bidang')) {
                $table->string('judul_tujuan_bidang')->default('Tujuan UNW Dalam Bidang')->nullable();
            }

            if (! Schema::hasColumn('vision_mission', 'tujuan_bidang')) {
                $table->text('tujuan_bidang')->nullable();
            }

            if (! Schema::hasColumn('vision_mission', 'judul_sasaran_target')) {
                $table->string('judul_sasaran_target')->default('Sasaran dan Target')->nullable();
            }

            if (! Schema::hasColumn('vision_mission', 'sasaran_target')) {
                $table->text('sasaran_target')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('vision_mission')) {
            return;
        }

        Schema::table('vision_mission', function (Blueprint $table): void {
            $columns = array_filter([
                Schema::hasColumn('vision_mission', 'judul_tujuan') ? 'judul_tujuan' : null,
                Schema::hasColumn('vision_mission', 'tujuan') ? 'tujuan' : null,
                Schema::hasColumn('vision_mission', 'judul_tujuan_bidang') ? 'judul_tujuan_bidang' : null,
                Schema::hasColumn('vision_mission', 'tujuan_bidang') ? 'tujuan_bidang' : null,
                Schema::hasColumn('vision_mission', 'judul_sasaran_target') ? 'judul_sasaran_target' : null,
                Schema::hasColumn('vision_mission', 'sasaran_target') ? 'sasaran_target' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
