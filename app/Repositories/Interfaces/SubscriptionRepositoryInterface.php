<?php

namespace App\Repositories\Interfaces;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

interface SubscriptionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * اشتراک‌های کاربر
     */
    public function getByUser(
        int $userId
    ): Collection;

    /**
     * اشتراک‌های فعال
     */
    public function getActive(): Collection;

    /**
     * اشتراک‌های منقضی شده
     */
    public function getExpired(): Collection;

    /**
     * اشتراک‌های لغو شده
     */
    public function getCancelled(): Collection;

    /**
     * فعال کردن اشتراک
     */
    public function activate(
        Subscription $subscription
    ): Subscription;

    /**
     * لغو اشتراک
     */
    public function cancel(
        Subscription $subscription
    ): Subscription;

    /**
     * تمدید اشتراک
     */
    public function extend(
        Subscription $subscription,
        int $days
    ): Subscription;
}
