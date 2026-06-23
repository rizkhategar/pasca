<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'sinta_lecturers',
        'pasca_lecturers',
        'sinta_lecturer_details',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'id')) {
                continue;
            }

            DB::statement("ALTER TABLE {$table} ADD COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE AFTER sinta_id");
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'id')) {
                continue;
            }

            DB::statement("ALTER TABLE {$table} DROP INDEX id");
            DB::statement("ALTER TABLE {$table} DROP COLUMN id");
        }
    }
};
