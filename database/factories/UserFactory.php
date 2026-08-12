<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * مدل مربوطه
     */
    protected $model = User::class;

    /**
     * تعریف داده‌های پیش‌فرض
     */
    public function definition(): array
    {
        return [

            'name' => fake()->name(),

            'mobile' => fake()->unique()->numerify('09#########'),

            'password' => Hash::make('123456'),

            'must_change_password' => false,

            'phone_verified_at' => now(),

            'is_active' => true,

            'last_login_at' => null,

            'remember_token' => null,

        ];
    }
}
