<?php

namespace App\Console\Commands;

use App\Jobs\SendSmsJob;
use App\Models\Subscription;
use Illuminate\Console\Command;

/**
 * یادآوری انقضای اشتراک
 * --------------------------------------------------------------------
 * هر روز اجرا می‌شود؛ به دانش‌آموزانی که اشتراکشان دقیقاً ۲ روز
 * دیگر تمام می‌شود، یک پیامک یادآوری می‌فرستد — تا فرصت تمدید
 * داشته باشند، بدون این‌که غافلگیر شوند.
 */
class NotifyExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:notify-expiring';

    protected $description = 'ارسال پیامک یادآوری به دانش‌آموزانی که اشتراکشان تا ۲ روز دیگر منقضی می‌شود';

    public function handle(): void
    {
        $targetDate = now()->addDays(2)->toDateString();

        $subscriptions = Subscription::query()
            ->where('status', 'active')
            ->whereDate('expires_at', $targetDate)
            ->with('user')
            ->get();

        $count = 0;

        foreach ($subscriptions as $subscription) {

            if (! $subscription->user?->mobile) {
                continue;
            }

            SendSmsJob::dispatch(
                $subscription->user->mobile,
                'اشتراک شما تا ۲ روز دیگر به پایان می‌رسد. برای ادامه‌ی استفاده، آن را تمدید کنید.'
            );

            $count++;
        }

        $this->info($count.' پیامک یادآوری ارسال شد.');
    }
}
