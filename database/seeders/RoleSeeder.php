<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * اجرای Seeder
     */
    public function run(): void
    {
        $roles = [

            'SuperAdmin',

            'Admin',

            'Teacher',

            'Student',

            'Parent',

        ];

        foreach ($roles as $role) {

            Role::firstOrCreate([

                'name' => $role,

                'guard_name' => 'web',

            ]);

        }
    }
}
