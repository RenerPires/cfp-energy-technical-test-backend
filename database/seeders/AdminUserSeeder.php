<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory(1)->create([
            'first_name' => 'admin',
            'last_name' => '',
            'username' => 'admin',
            'email' => 'admin@cfp.energy',
            'password' => Hash::make('changeThisPassword'),
            'profile_picture_url' => 'https://pub-c7474b88df8541a3b88171d947269af2.r2.dev/profile-picture/cfpenergy_logo.jpg',
            'is_active' => true,
        ])->first();

        $user->assignRole('admin');
    }
}
