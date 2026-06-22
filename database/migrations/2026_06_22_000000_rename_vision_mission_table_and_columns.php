<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vision_mission') && ! Schema::hasTable('vision_missions')) {
            Schema::rename('vision_mission', 'vision_missions');
        }

        if (! Schema::hasTable('vision_missions')) {
            return;
        }

        Schema::table('vision_missions', function (Blueprint $table): void {
            if (Schema::hasColumn('vision_missions', 'judul_visi') && ! Schema::hasColumn('vision_missions', 'vision_title')) {
                $table->renameColumn('judul_visi', 'vision_title');
            }

            if (Schema::hasColumn('vision_missions', 'visi') && ! Schema::hasColumn('vision_missions', 'vision')) {
                $table->renameColumn('visi', 'vision');
            }

            if (Schema::hasColumn('vision_missions', 'judul_misi') && ! Schema::hasColumn('vision_missions', 'mission_title')) {
                $table->renameColumn('judul_misi', 'mission_title');
            }

            if (Schema::hasColumn('vision_missions', 'misi') && ! Schema::hasColumn('vision_missions', 'mission')) {
                $table->renameColumn('misi', 'mission');
            }

            if (Schema::hasColumn('vision_missions', 'judul_tujuan') && ! Schema::hasColumn('vision_missions', 'objectives_title')) {
                $table->renameColumn('judul_tujuan', 'objectives_title');
            }

            if (Schema::hasColumn('vision_missions', 'tujuan') && ! Schema::hasColumn('vision_missions', 'objectives')) {
                $table->renameColumn('tujuan', 'objectives');
            }

            if (Schema::hasColumn('vision_missions', 'judul_tujuan_bidang') && ! Schema::hasColumn('vision_missions', 'field_objectives_title')) {
                $table->renameColumn('judul_tujuan_bidang', 'field_objectives_title');
            }

            if (Schema::hasColumn('vision_missions', 'tujuan_bidang') && ! Schema::hasColumn('vision_missions', 'field_objectives')) {
                $table->renameColumn('tujuan_bidang', 'field_objectives');
            }

            if (Schema::hasColumn('vision_missions', 'judul_sasaran_target') && ! Schema::hasColumn('vision_missions', 'goals_targets_title')) {
                $table->renameColumn('judul_sasaran_target', 'goals_targets_title');
            }

            if (Schema::hasColumn('vision_missions', 'sasaran_target') && ! Schema::hasColumn('vision_missions', 'goals_targets')) {
                $table->renameColumn('sasaran_target', 'goals_targets');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('vision_missions')) {
            return;
        }

        Schema::table('vision_missions', function (Blueprint $table): void {
            if (Schema::hasColumn('vision_missions', 'vision_title') && ! Schema::hasColumn('vision_missions', 'judul_visi')) {
                $table->renameColumn('vision_title', 'judul_visi');
            }

            if (Schema::hasColumn('vision_missions', 'vision') && ! Schema::hasColumn('vision_missions', 'visi')) {
                $table->renameColumn('vision', 'visi');
            }

            if (Schema::hasColumn('vision_missions', 'mission_title') && ! Schema::hasColumn('vision_missions', 'judul_misi')) {
                $table->renameColumn('mission_title', 'judul_misi');
            }

            if (Schema::hasColumn('vision_missions', 'mission') && ! Schema::hasColumn('vision_missions', 'misi')) {
                $table->renameColumn('mission', 'misi');
            }

            if (Schema::hasColumn('vision_missions', 'objectives_title') && ! Schema::hasColumn('vision_missions', 'judul_tujuan')) {
                $table->renameColumn('objectives_title', 'judul_tujuan');
            }

            if (Schema::hasColumn('vision_missions', 'objectives') && ! Schema::hasColumn('vision_missions', 'tujuan')) {
                $table->renameColumn('objectives', 'tujuan');
            }

            if (Schema::hasColumn('vision_missions', 'field_objectives_title') && ! Schema::hasColumn('vision_missions', 'judul_tujuan_bidang')) {
                $table->renameColumn('field_objectives_title', 'judul_tujuan_bidang');
            }

            if (Schema::hasColumn('vision_missions', 'field_objectives') && ! Schema::hasColumn('vision_missions', 'tujuan_bidang')) {
                $table->renameColumn('field_objectives', 'tujuan_bidang');
            }

            if (Schema::hasColumn('vision_missions', 'goals_targets_title') && ! Schema::hasColumn('vision_missions', 'judul_sasaran_target')) {
                $table->renameColumn('goals_targets_title', 'judul_sasaran_target');
            }

            if (Schema::hasColumn('vision_missions', 'goals_targets') && ! Schema::hasColumn('vision_missions', 'sasaran_target')) {
                $table->renameColumn('goals_targets', 'sasaran_target');
            }
        });

        if (Schema::hasTable('vision_missions') && ! Schema::hasTable('vision_mission')) {
            Schema::rename('vision_missions', 'vision_mission');
        }
    }
};
