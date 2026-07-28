<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinta_lecturer_fetch_all_schedule_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(false);
            $table->time('scheduled_time')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_skip_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinta_lecturer_fetch_all_schedule_settings');
    }
};
