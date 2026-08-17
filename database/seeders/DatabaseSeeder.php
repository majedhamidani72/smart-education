<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([

            RoleSeeder::class,

            PermissionSeeder::class,

            AssignRolePermissionSeeder::class,

            SuperAdminSeeder::class,

            ContentTypeSeeder::class,

            TestEducationSeeder::class,

            SettingSeeder::class,

        ]);
    }
}
