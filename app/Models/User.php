<?php

namespace App\Models;

use Filament\Panel;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use SoftDeletes;


    protected $fillable = [

        'name',

        'mobile',

    ];


    protected $hidden = [

        'password',

        'remember_token',

    ];


    protected function casts(): array
    {
        return [

            'phone_verified_at' => 'datetime',

            'last_login_at' => 'datetime',

            'is_active' => 'boolean',

        ];
    }



    public function canAccessPanel(
        Panel $panel
    ): bool {

        return $this->is_active
            &&
            (
                $this->hasRole('SuperAdmin')
                ||
                $this->hasRole('Admin')
            );

    }



    public function otpCodes(): HasMany
    {
        return $this->hasMany(
            OtpCode::class
        );
    }



    public function purchases(): HasMany
    {
        return $this->hasMany(
            Purchase::class
        );
    }



    public function subscriptions(): HasMany
    {
        return $this->hasMany(
            Subscription::class
        );
    }



    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(
            PaymentTransaction::class
        );
    }



    public function uploadedVideos(): HasMany
    {
        return $this->hasMany(
            Video::class,
            'uploaded_by'
        );
    }



    public function approvedVideos(): HasMany
    {
        return $this->hasMany(
            Video::class,
            'approved_by'
        );
    }



    public function quizzes(): HasMany
    {
        return $this->hasMany(
            Quiz::class,
            'created_by'
        );
    }



    public function systemLogs(): HasMany
    {
        return $this->hasMany(
            SystemLog::class
        );
    }



    public function teacherAgreements(): HasMany
    {
        return $this->hasMany(
            TeacherAgreement::class,
            'teacher_id'
        );
    }



    public function devices(): HasMany
    {
        return $this->hasMany(
            Device::class
        );
    }
}
