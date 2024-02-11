<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Enums\PermissionTypes;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        $adminRole->givePermissionTo(PermissionTypes::viewUsers, PermissionTypes::createUsers, PermissionTypes::updateUsers, PermissionTypes::deleteUsers, PermissionTypes::inactivateUsers, PermissionTypes::activateUsers, PermissionTypes::grantPermissions, PermissionTypes::revokePermissions);
        $userRole->givePermissionTo(PermissionTypes::viewUsers);
    }
}
