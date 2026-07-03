<?php

use App\Models\Contacts;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')
                ->where('model_type', 'App\\Models\\User')
                ->update(['model_type' => User::class]);
        }

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')
                ->where('model_type', 'App\\Models\\User')
                ->update(['model_type' => User::class]);
        }

        if (Schema::hasTable('activity_logs')) {
            DB::table('activity_logs')
                ->where('causer_type', 'App\\Models\\User')
                ->update(['causer_type' => User::class]);

            DB::table('activity_logs')
                ->where('subject_type', 'App\\Models\\User')
                ->update(['subject_type' => User::class]);

            DB::table('activity_logs')
                ->where('subject_type', 'App\\Models\\Contact')
                ->update(['subject_type' => Contacts::class]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->update(['model_type' => 'App\\Models\\User']);
        }

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')
                ->where('model_type', User::class)
                ->update(['model_type' => 'App\\Models\\User']);
        }

        if (Schema::hasTable('activity_logs')) {
            DB::table('activity_logs')
                ->where('causer_type', User::class)
                ->update(['causer_type' => 'App\\Models\\User']);

            DB::table('activity_logs')
                ->where('subject_type', User::class)
                ->update(['subject_type' => 'App\\Models\\User']);

            DB::table('activity_logs')
                ->where('subject_type', Contacts::class)
                ->update(['subject_type' => 'App\\Models\\Contact']);
        }
    }
};
