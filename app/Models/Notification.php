<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | فیلدهای قابل ثبت
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'user_id',

        'title',

        'message',

        'type',

        'action_url',

        'is_read',

        'read_at',

        'data',

    ];

    /*
    |--------------------------------------------------------------------------
    | تبدیل نوع داده‌ها
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [

            'is_read' => 'boolean',

            'read_at' => 'datetime',

            'data' => 'array',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | روابط
    |--------------------------------------------------------------------------
    */

    /**
     * کاربر
     */
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | متدهای کمکی
    |--------------------------------------------------------------------------
    */

    /**
     * خوانده شده؟
     */
    public function isRead(): bool
    {
        return $this->is_read;
    }

    /**
     * خواندن اعلان
     */
    public function markAsRead(): void
    {
        $this->update([

            'is_read' => true,

            'read_at' => now(),

        ]);
    }

    /**
     * خواندن نشده؟
     */
    public function isUnread(): bool
    {
        return !$this->is_read;
    }
}
