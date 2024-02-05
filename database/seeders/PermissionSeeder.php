<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'view-users']);
        Permission::create(['name' => 'create-users']);
        Permission::create(['name' => 'update-users']);
        Permission::create(['name' => 'delete-users']);
        Permission::create(['name' => 'inactivate-users']);
        Permission::create(['name' => 'activate-users']);
        Permission::create(['name' => 'grant-permissions']);
        Permission::create(['name' => 'revoke-permissions']);
    }
}
