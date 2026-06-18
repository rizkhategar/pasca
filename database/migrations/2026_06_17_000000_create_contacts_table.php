<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->string('primary_admin_name')->default('Admin 1');
            $table->string('primary_whatsapp')->default('+62 857-3033-9469');
            $table->string('secondary_admin_name')->default('Admin 2');
            $table->string('secondary_whatsapp')->default('+62 811-2758-575');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
