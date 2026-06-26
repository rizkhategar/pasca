<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vision_mission')) {
            Schema::table('vision_mission', function (Blueprint $table): void {
                if (! Schema::hasColumn('vision_mission', 'judul_visi')) {
                    $table->string('judul_visi')->default('Visi')->after('id');
                }

                if (! Schema::hasColumn('vision_mission', 'judul_misi')) {
                    $table->string('judul_misi')->default('Misi')->after('visi');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vision_mission')) {
            Schema::table('vision_mission', function (Blueprint $table): void {
                $columns = array_filter([
                    Schema::hasColumn('vision_mission', 'judul_visi') ? 'judul_visi' : null,
                    Schema::hasColumn('vision_mission', 'judul_misi') ? 'judul_misi' : null,
                ]);

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
