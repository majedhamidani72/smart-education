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



class User extends Authenticatable implements FilamentUser, \Filament\Models\Contracts\HasAvatar
{


    use HasApiTokens;

    use HasFactory;

    use Notifiable;

    use HasRoles;

    use SoftDeletes;





    protected $fillable = [


        'name',


        'mobile',


        'avatar',


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


    /**
     * آیا این کاربر به یک محتوای مشخص دسترسی دارد؟
     * --------------------------------------------------------------------
     * منطق: اشتراک‌های فعال کاربر را می‌گیرد و بررسی می‌کند آیا
     * پلن هرکدام دقیقاً به «همان کتاب» وصل است (برای پایه‌های
     * ۷ تا ۱۲، هر کتاب جداگانه خریداری می‌شود)، یا به «همان پایه»
     * وصل است (برای پایه‌های ۱ تا ۶، یک خرید کل پایه را باز
     * می‌کند). چون Plan.planable چندریختی است، هر دو حالت را با
     * همین یک متد پوشش می‌دهیم.
     */
    public function hasAccessToContentItem(
        \App\Models\ContentItem $contentItem
    ): bool {

        $chapter = $contentItem->chapter()
            ->with('book.appGradeSubject')
            ->first();

        if (! $chapter || ! $chapter->book) {

            // اگر مسیر آموزشی محتوا مشخص نباشد، به‌صورت پیش‌فرض
            // دسترسی داده نمی‌شود (احتیاط، نه سهل‌گیری).
            return false;
        }

        $book = $chapter->book;

        $gradeId = $book->appGradeSubject?->grade_id;

        $activeSubscriptions = $this->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>=', now())
            ->with('plan')
            ->get();

        foreach ($activeSubscriptions as $subscription) {

            $plan = $subscription->plan;

            if (! $plan) {
                continue;
            }

            // دسترسی «همین کتاب» (پایه‌های ۷ تا ۱۲)
            if (
                $plan->planable_type === \App\Models\Book::class
                && (int) $plan->planable_id === (int) $book->id
            ) {
                return true;
            }

            // دسترسی «کل پایه» (پایه‌های ۱ تا ۶)
            if (
                $plan->planable_type === \App\Models\Grade::class
                && $gradeId
                && (int) $plan->planable_id === (int) $gradeId
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * آیا این کاربر (دانش‌آموز) به یک آزمون مشخص دسترسی دارد؟
     * همان منطق hasAccessToContentItem، فقط چون آزمون می‌تواند
     * مستقیم به کتاب، فصل، یا بخش وصل باشد (رابطه‌ی چندریختی
     * quizable)، اول باید کتابِ اصلی از هرکدام پیدا شود.
     */
    public function hasAccessToQuiz(
        \App\Models\Quiz $quiz
    ): bool {

        $book = match ($quiz->quizable_type) {

            \App\Models\Book::class => $quiz->quizable,

            \App\Models\Chapter::class => $quiz->quizable?->book,

            \App\Models\Section::class => $quiz->quizable?->chapter?->book,

            default => null,
        };

        if (! $book) {
            return false;
        }

        $gradeId = $book->appGradeSubject?->grade_id;

        $activeSubscriptions = $this->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>=', now())
            ->with('plan')
            ->get();

        foreach ($activeSubscriptions as $subscription) {

            $plan = $subscription->plan;

            if (! $plan) {
                continue;
            }

            if (
                $plan->planable_type === \App\Models\Book::class
                && (int) $plan->planable_id === (int) $book->id
            ) {
                return true;
            }

            if (
                $plan->planable_type === \App\Models\Grade::class
                && $gradeId
                && (int) $plan->planable_id === (int) $gradeId
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * پروفایل معلم — فقط برای کاربرهایی معنا دارد که نقش معلم
     * دارند، ولی رابطه برای همه‌ی کاربرها تعریف شده (اگر رکوردی
     * نباشد، فقط null برمی‌گردد).
     */
    public function teacherProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\TeacherProfile::class);
    }

    /**
     * عکس پروفایل که Filament بالای هر صفحه (کنار نام کاربر)
     * نشان می‌دهد — از همان عکسی که خودِ معلم توی «پروفایل من»
     * آپلود کرده می‌آید.
     */
    /**
     * عکس پروفایل که Filament بالای هر صفحه (کنار نام کاربر)
     * نشان می‌دهد. اول ستون عمومی avatar (که هر نقشی، از جمله
     * ادمین، می‌تواند داشته باشد) چک می‌شود؛ اگر نبود، برای
     * سازگاری با معلم‌هایی که از قبل عکس‌شان را جای دیگری
     * (teacher_profiles) گذاشته بودند، آن مسیر هم امتحان می‌شود.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar) {

            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->avatar);
        }

        return $this->teacherProfile?->photo_url;
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
