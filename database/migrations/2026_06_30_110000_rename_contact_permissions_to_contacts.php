<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->renamePermissionSubject('Contact', 'Contacts');
    }

    public function down(): void
    {
        $this->renamePermissionSubject('Contacts', 'Contact');
    }

    private function renamePermissionSubject(string $from, string $to): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        DB::table('permissions')
            ->where('name', 'like', '%:' . $from)
            ->orderBy('id')
            ->select(['id', 'name', 'guard_name'])
            ->chunkById(100, function ($permissions) use ($from, $to): void {
                foreach ($permissions as $permission) {
                    $targetName = preg_replace('/:' . preg_quote($from, '/') . '$/', ':' . $to, $permission->name);

                    if (! is_string($targetName) || $targetName === $permission->name) {
                        continue;
                    }

                    $existingPermissionId = DB::table('permissions')
                        ->where('name', $targetName)
                        ->where('guard_name', $permission->guard_name)
                        ->value('id');

                    if ($existingPermissionId && (int) $existingPermissionId !== (int) $permission->id) {
                        if (Schema::hasTable('role_has_permissions')) {
                            DB::table('role_has_permissions')
                                ->where('permission_id', $permission->id)
                                ->update(['permission_id' => $existingPermissionId]);
                        }

                        if (Schema::hasTable('model_has_permissions')) {
                            DB::table('model_has_permissions')
                                ->where('permission_id', $permission->id)
                                ->update(['permission_id' => $existingPermissionId]);
                        }

                        DB::table('permissions')->where('id', $permission->id)->delete();

                        continue;
                    }

                    DB::table('permissions')
                        ->where('id', $permission->id)
                        ->update(['name' => $targetName]);
                }
            });
    }
};
