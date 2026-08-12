<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * ایجاد اولین مدیر سیستم
     */
    public function run(): void
    {
        $user = User::updateOrCreate(

            [
                'mobile' => '09120000000',
            ],

            [
                'name' => 'Super Admin',

                'password' => Hash::make('12345678'),

                'must_change_password' => true,

                'phone_verified_at' => now(),

                'is_active' => true,

                'last_login_at' => null,
            ]

        );

        $user->syncRoles([
            'SuperAdmin',
        ]);
    }
}
