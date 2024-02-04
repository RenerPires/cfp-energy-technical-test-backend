<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $user = User::factory(1)->create([
            'first_name' => 'admin',
            'last_name' => '',
            'username' => 'admin',
            'email' => 'admin@cfp.energy',
            'password' => Hash::make('changeThisPassword'),
        ])->first();

        $user->assignRole('admin');
    }
}
