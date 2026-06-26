<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vision_missions')) {
            return;
        }

        if (Schema::hasTable('vision_mission')) {
            Schema::rename('vision_mission', 'vision_missions');
            $this->syncOldColumns();

            return;
        }

        Schema::create('vision_missions', function (Blueprint $table) {
            $table->id();
            $table->string('vision_title')->default('Vision');
            $table->text('vision');
            $table->string('mission_title')->default('Mission');
            $table->text('mission');
            $table->string('objectives_title')->default('Objectives')->nullable();
            $table->text('objectives')->nullable();
            $table->string('field_objectives_title')->default('UNW Field Objectives')->nullable();
            $table->text('field_objectives')->nullable();
            $table->string('goals_targets_title')->default('Goals and Targets')->nullable();
            $table->text('goals_targets')->nullable();
            $table->string('hero_title')->default('Vision & Mission')->nullable();
            $table->string('hero_subtitle')->default('Postgraduate School Universitas Ngudi Waluyo')->nullable();
            $table->string('hero_image')->nullable();
            $table->timestamps();
        });
    }

    private function syncOldColumns(): void
    {
        $renames = [
            'judul_visi' => 'vision_title',
            'visi' => 'vision',
            'judul_misi' => 'mission_title',
            'misi' => 'mission',
            'judul_tujuan' => 'objectives_title',
            'tujuan' => 'objectives',
            'judul_tujuan_bidang' => 'field_objectives_title',
            'tujuan_bidang' => 'field_objectives',
            'judul_sasaran_target' => 'goals_targets_title',
            'sasaran_target' => 'goals_targets',
        ];

        foreach ($renames as $oldColumn => $newColumn) {
            if (Schema::hasColumn('vision_missions', $oldColumn) && ! Schema::hasColumn('vision_missions', $newColumn)) {
                Schema::table('vision_missions', function (Blueprint $table) use ($oldColumn, $newColumn): void {
                    $table->renameColumn($oldColumn, $newColumn);
                });
            }
        }

        Schema::table('vision_missions', function (Blueprint $table): void {
            if (! Schema::hasColumn('vision_missions', 'vision_title')) {
                $table->string('vision_title')->default('Vision')->after('id');
            }

            if (! Schema::hasColumn('vision_missions', 'mission_title')) {
                $table->string('mission_title')->default('Mission')->after('vision');
            }

            if (! Schema::hasColumn('vision_missions', 'objectives_title')) {
                $table->string('objectives_title')->default('Objectives')->nullable();
            }

            if (! Schema::hasColumn('vision_missions', 'objectives')) {
                $table->text('objectives')->nullable();
            }

            if (! Schema::hasColumn('vision_missions', 'field_objectives_title')) {
                $table->string('field_objectives_title')->default('UNW Field Objectives')->nullable();
            }

            if (! Schema::hasColumn('vision_missions', 'field_objectives')) {
                $table->text('field_objectives')->nullable();
            }

            if (! Schema::hasColumn('vision_missions', 'goals_targets_title')) {
                $table->string('goals_targets_title')->default('Goals and Targets')->nullable();
            }

            if (! Schema::hasColumn('vision_missions', 'goals_targets')) {
                $table->text('goals_targets')->nullable();
            }

            if (! Schema::hasColumn('vision_missions', 'hero_title')) {
                $table->string('hero_title')->default('Vision & Mission')->nullable();
            }

            if (! Schema::hasColumn('vision_missions', 'hero_subtitle')) {
                $table->string('hero_subtitle')->default('Postgraduate School Universitas Ngudi Waluyo')->nullable();
            }

            if (! Schema::hasColumn('vision_missions', 'hero_image')) {
                $table->string('hero_image')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vision_missions');
    }
};
