<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();



        // =====================
        // Permissions
        // =====================

        $permissions = [

            'manage users',

            'manage teachers',

            'manage apps',

            'manage grades',

            'manage subjects',

            'manage lessons',

            'manage contents',

            'approve contents',

            'delete contents',

            'manage quizzes',

            'manage purchases',

            'manage payments',

            'manage advertisements',

            'manage settings',

            'view reports',

            'view own earnings',

        ];


        foreach ($permissions as $permission) {

            Permission::firstOrCreate([
                'name' => $permission
            ]);

        }



        // =====================
        // Roles
        // =====================

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin'
        ]);


        $admin = Role::firstOrCreate([
            'name' => 'Admin'
        ]);


        $teacher = Role::firstOrCreate([
            'name' => 'Teacher'
        ]);



        // =====================
        // Assign
        // =====================


        // همه دسترسی‌ها
        $superAdmin->givePermissionTo(
            Permission::all()
        );



        // ادمین
        $admin->givePermissionTo(
            Permission::all()
        );



        // معلم
        $teacher->givePermissionTo([

            'manage contents',

            'manage quizzes',

            'view own earnings',

        ]);

    }
}
