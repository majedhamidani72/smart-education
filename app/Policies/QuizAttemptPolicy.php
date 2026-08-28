<?php

namespace App\Policies;

use App\Models\QuizAttempt;
use App\Models\User;

class QuizAttemptPolicy
{
    /** دانش‌آموز فقط گزارش تلاش خودش را می‌بیند. */
    public function view(User $user, QuizAttempt $attempt): bool
    {
        return (int) $attempt->user_id === (int) $user->id
            || $user->hasAnyRole(['SuperAdmin', 'Admin']);
    }

    /** ثبت پاسخ و پایان آزمون فقط برای تلاش در حال اجرای خود کاربر مجاز است. */
    public function update(User $user, QuizAttempt $attempt): bool
    {
        return (int) $attempt->user_id === (int) $user->id
            && $attempt->status === 'started';
    }
}
