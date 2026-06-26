<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $column = 'whatsapp_' . 'admins';
        $afterColumn = 'secondary_' . 'whatsapp';

        if (! Schema::hasTable('contacts')) {
            return;
        }

        if (Schema::hasColumn('contacts', $column)) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) use ($column, $afterColumn): void {
            $table->json($column)->nullable()->after($afterColumn);
        });
    }

    public function down(): void
    {
        $column = 'whatsapp_' . 'admins';

        if (! Schema::hasTable('contacts')) {
            return;
        }

        if (! Schema::hasColumn('contacts', $column)) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) use ($column): void {
            $table->dropColumn($column);
        });
    }
};
