<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinta_lecturer_automatic_runs', function (Blueprint $table): void {
            $table->id();
            $table->date('run_date')->index();
            $table->string('scheduled_time', 5)->nullable();
            $table->foreignId('fetch_batch_id')->nullable()->constrained('sinta_lecturer_fetch_batches')->nullOnDelete();
            $table->string('status')->default('queued')->index();
            $table->string('phase')->nullable()->index();
            $table->timestamp('fetch_started_at')->nullable();
            $table->timestamp('fetch_finished_at')->nullable();
            $table->timestamp('import_started_at')->nullable();
            $table->timestamp('import_finished_at')->nullable();
            $table->json('failed_sinta_ids')->nullable();
            $table->json('missing_study_program_sinta_ids')->nullable();
            $table->longText('error_message')->nullable();
            $table->longText('summary_message')->nullable();
            $table->timestamps();

            $table->unique(['run_date', 'scheduled_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinta_lecturer_automatic_runs');
    }
};
