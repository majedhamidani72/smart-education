<?php

namespace App\Repositories\Eloquent;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;

class SubscriptionRepository extends BaseRepository implements SubscriptionRepositoryInterface
{
    public function __construct(
        Subscription $model
    ) {
        parent::__construct($model);
    }

    /**
     * اشتراک‌های یک کاربر
     */
    public function getByUser(
        int $userId
    ): Collection
    {
        return $this->model
            ->where(
                'user_id',
                $userId
            )
            ->latest()
            ->get();
    }

    /**
     * اشتراک‌های فعال
     */
    public function getActive(): Collection
    {
        return $this->model
            ->where(
                'status',
                'active'
            )
            ->where(
                'expires_at',
                '>=',
                now()
            )
            ->latest()
            ->get();
    }

    /**
     * اشتراک‌های منقضی شده
     */
    public function getExpired(): Collection
    {
        return $this->model
            ->where(
                'expires_at',
                '<',
                now()
            )
            ->latest()
            ->get();
    }

    /**
     * اشتراک‌های لغو شده
     */
    public function getCancelled(): Collection
    {
        return $this->model
            ->where(
                'status',
                'cancelled'
            )
            ->latest()
            ->get();
    }

    /**
     * فعال کردن اشتراک
     */
    public function activate(
        Subscription $subscription
    ): Subscription
    {
        $subscription->update([

            'status' => 'active',

            'cancelled_at' => null,

        ]);

        return $subscription->fresh();
    }

    /**
     * لغو اشتراک
     */
    public function cancel(
        Subscription $subscription
    ): Subscription
    {
        $subscription->update([

            'status' => 'cancelled',

            'cancelled_at' => now(),

        ]);

        return $subscription->fresh();
    }

    /**
     * تمدید اشتراک
     */
    public function extend(
        Subscription $subscription,
        int $days
    ): Subscription
    {
        $subscription->update([

            'expires_at' => $subscription
                ->expires_at
                ->copy()
                ->addDays($days),

        ]);

        return $subscription->fresh();
    }
}
