<?php

use App\Models\Users;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        if (Schema::hasTable('roles') && Schema::hasTable('model_has_roles')) {
            DB::table('users')
                ->whereNotNull('role')
                ->where('role', '!=', '')
                ->orderBy('id')
                ->select(['id', 'role'])
                ->chunkById(100, function ($users): void {
                    foreach ($users as $user) {
                        $roleName = trim((string) $user->role);

                        if ($roleName === '') {
                            continue;
                        }

                        $roleId = DB::table('roles')
                            ->where('name', $roleName)
                            ->where('guard_name', 'web')
                            ->value('id');

                        if (! $roleId) {
                            $roleId = DB::table('roles')->insertGetId([
                                'name' => $roleName,
                                'guard_name' => 'web',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        DB::table('model_has_roles')->updateOrInsert([
                            'role_id' => $roleId,
                            'model_type' => User::class,
                            'model_id' => $user->id,
                        ]);
                    }
                });
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('role')->nullable()->after('password');
            });
        }
    }
};
