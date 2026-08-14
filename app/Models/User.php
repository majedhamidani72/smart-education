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


        'password',


        'must_change_password',


        'is_active',


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


            'password' => 'hashed',


            'must_change_password' => 'boolean',


            'is_active' => 'boolean',



        ];
    }








    /*
    |--------------------------------------------------------------------------
    | Filament Panel Access
    |--------------------------------------------------------------------------
    */


    public function canAccessPanel(
        Panel $panel
    ): bool {


        return $this->is_active

            &&

            (

                $this->hasRole('SuperAdmin')

                ||

                $this->hasRole('Admin')
                
                ||

                $this->hasRole('Teacher')


            );
    }


    /*
    |--------------------------------------------------------------------------
    | OTP
    |--------------------------------------------------------------------------
    */


    public function otpCodes(): HasMany
    {

        return $this->hasMany(
            OtpCode::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Purchases
    |--------------------------------------------------------------------------
    */


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


    /*
    |--------------------------------------------------------------------------
    | Content
    |--------------------------------------------------------------------------
    */


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


    /*
    |--------------------------------------------------------------------------
    | Teacher
    |--------------------------------------------------------------------------
    */


    public function teacherAgreements(): HasMany
    {

        return $this->hasMany(
            TeacherAgreement::class,
            'teacher_id'
        );
    }



    public function teacherAssignments(): HasMany
    {

        return $this->hasMany(
            TeacherAssignment::class,
            'teacher_id'
        );
    }


    public function assignedTeacherAssignments(): HasMany
    {

        return $this->hasMany(
            TeacherAssignment::class,
            'assigned_by'
        );
    }


    public function devices(): HasMany
    {

        return $this->hasMany(
            Device::class
        );
    }
}
