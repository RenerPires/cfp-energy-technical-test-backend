<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        $adminRole->givePermissionTo('view-users', 'create-users', 'update-users', 'delete-users', 'grant-permissions', 'revoke-permissions', 'inactivate-users', 'activate-users');
        $userRole->givePermissionTo('view-users');
    }
}
