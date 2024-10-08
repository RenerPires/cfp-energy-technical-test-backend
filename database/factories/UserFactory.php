<?php

namespace Database\Factories;

use App\Models\User;
use Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $first_name = fake()->firstName();
        $last_name = fake()->lastName();
        return [
            'id' => Uuid::uuid4(),
            'first_name' => $first_name,
            'last_name' => $last_name,
            'username' => fake()->unique()->userName(),
            'phone_number' => fake()->unique()->phoneNumber(),
            'date_of_birth' => fake()->dateTimeBetween('-30 years', '-18 years')->format('Y-m-d'),
            'email' => "$first_name.$last_name@uorak.com",
            'email_verified_at' => now(),
            'profile_picture_url' => "https://ui-avatars.com/api/?name={$first_name}+{$last_name}&background=random&format=png",
            'is_active' => true,
            'password' => Hash::make('password'), // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
