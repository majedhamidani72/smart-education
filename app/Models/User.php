<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable
{
    // قابلیت API، Factory، اعلان، نقش‌ها و حذف نرم
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;



    // فیلدهایی که اجازه Mass Assignment دارند
    protected $fillable = [

        'name',
        // نام کاربر


        'mobile',
        // شماره موبایل

    ];



    // فیلدهایی که نباید در خروجی نمایش داده شوند
    protected $hidden = [

        'password',
        // رمز عبور


        'remember_token',
        // توکن نشست لاراول

    ];



    // تبدیل خودکار نوع داده‌ها
    protected function casts(): array
{
    return [

        'phone_verified_at' => 'datetime',

        'last_login_at' => 'datetime',

        'is_active' => 'boolean',

    ];
}



    // =========================
    // Relationships
    // =========================


    // هر کاربر چند OTP دارد
    public function otpCodes()
    {
        return $this->hasMany(
            OtpCode::class
        );
    }



    // هر کاربر چند خرید دارد
    public function purchases()
    {
        return $this->hasMany(
            Purchase::class
        );
    }



    // هر کاربر چند اشتراک دارد
    public function subscriptions()
    {
        return $this->hasMany(
            Subscription::class
        );
    }



    // تراکنش‌های پرداخت کاربر
    public function paymentTransactions()
    {
        return $this->hasMany(
            PaymentTransaction::class
        );
    }



    // ویدئوهای ساخته شده توسط معلم
    public function videos()
    {
        return $this->hasMany(
            Video::class,
            'teacher_id'
        );
    }



    // آزمون‌های ساخته شده توسط کاربر
    public function quizzes()
    {
        return $this->hasMany(
            Quiz::class,
            'created_by'
        );
    }



    // لاگ‌های ثبت شده توسط کاربر
    public function systemLogs()
    {
        return $this->hasMany(
            SystemLog::class
        );
    }



    // رضایتنامه‌های معلم
    public function teacherAgreements()
    {
        return $this->hasMany(
            TeacherAgreement::class,
            'teacher_id'
        );
    }



    // دستگاه‌های متصل کاربر
    public function devices()
    {
        return $this->hasMany(
            Device::class
        );
    }

}
