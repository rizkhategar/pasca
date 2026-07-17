<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinta_lecturer_fetch_batches', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedInteger('processed_items')->default(0);
            $table->unsignedInteger('success_items')->default(0);
            $table->unsignedInteger('warning_items')->default(0);
            $table->unsignedInteger('failed_items')->default(0);
            $table->string('current_sinta_id')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sinta_lecturer_fetch_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('sinta_lecturer_fetch_batches')->cascadeOnDelete();
            $table->string('sinta_id')->index();
            $table->string('lecturer_name')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('import_status')->default('not_ready')->index();
            $table->longText('log_output')->nullable();
            $table->text('error_message')->nullable();
            $table->text('warning_message')->nullable();
            $table->text('import_error')->nullable();
            $table->unsignedSmallInteger('retry_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'sinta_id']);
        });

        Schema::create('sinta_lecturer_study_program_settings', function (Blueprint $table) {
            $table->id();
            $table->string('sinta_id')->index();
            $table->foreignId('study_program_id')->constrained('study_programs')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['sinta_id', 'study_program_id'], 'sinta_lecturer_study_program_settings_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinta_lecturer_study_program_settings');
        Schema::dropIfExists('sinta_lecturer_fetch_batch_items');
        Schema::dropIfExists('sinta_lecturer_fetch_batches');
    }
};
