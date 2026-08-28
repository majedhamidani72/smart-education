<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    /**
     * ایجاد اولین مدیر سیستم
     */
    public function run(): void
    {
        $mobile = (string) config('admin.super_admin.mobile');
        $password = (string) config('admin.super_admin.password');

        if (! preg_match('/^09\d{9}$/', $mobile) || strlen($password) < 8) {
            throw new RuntimeException(
                'SUPER_ADMIN_MOBILE and SUPER_ADMIN_PASSWORD must be set correctly in .env.'
            );
        }

        DB::transaction(function () use ($mobile, $password): void {
            // شماره موبایل هویت یکتای کاربر است. اگر قبلاً در سایت ثبت‌نام
            // کرده باشد، همان حساب ارتقا می‌یابد و داده‌هایش حفظ می‌شود.
            $user = User::withTrashed()->firstOrNew(['mobile' => $mobile]);
            $user->fill([
                'name' => config('admin.super_admin.name'),
                'password' => Hash::make($password),
                'must_change_password' => false,
                'is_active' => true,
            ]);
            $user->phone_verified_at ??= now();
            $user->deleted_at = null;
            $user->save();
            $user->syncRoles(['SuperAdmin']);

            // این سامانه یک سوپرادمین اصلی دارد. حساب سیدشدهٔ قدیمی
            // غیرفعال می‌شود، ولی حذف نمی‌شود تا کلیدهای خارجی محفوظ بمانند.
            User::role('SuperAdmin')
                ->whereKeyNot($user->getKey())
                ->get()
                ->each(function (User $oldSuperAdmin): void {
                    $oldSuperAdmin->removeRole('SuperAdmin');
                    $oldSuperAdmin->forceFill(['is_active' => false])->save();
                });
        });
    }
}
