<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Periode;
use App\Models\Organisasi;
use App\Models\OrganisasiPeriode;
use App\Models\UserOrganisasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class UserRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // add user data
        if (!User::where('email', 'superadmin@mail.com')->first()) {
            DB::table('users')->insert([
                'name' => 'Super Admin',
                'email' => 'superadmin@mail.com',
                'password' => Hash::make('SUPERadminpasca1^'),
            ]);
        }
        if (!User::where('email', 'admin@mail.co')->first()) {
            DB::table('users')->insert([
                'name' => 'admin',
                'email' => 'admin@mail.com',
                'password' => Hash::make('ADMINpasca1^'),
            ]);
        }

        // truncate all role permission table
        // https://medium.com/@th.ucsy/disabling-foreign-key-constraint-in-laravel-orms-truncate-function-2bebcaecf8ed
        Schema::disableForeignKeyConstraints();
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        Schema::enableForeignKeyConstraints();

        // add role data
        Role::insert(
            [
                [
                    'name' => 'super_admin',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'admin',
                    'guard_name' => 'web'
                ],
            ]
        );

        // Assign role to user
        $userSuperAdmin = User::where('email', 'superadmin@mail.com')->first();
        $userSuperAdmin->syncRoles(['super_admin']);

        $userAdmin = User::where('email', 'admin@mail.com')->first();
        $userAdmin->syncRoles(['admin']);
    }
}
