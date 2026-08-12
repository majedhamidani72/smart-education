<?php

namespace App\Services;

use Throwable;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;

class SubscriptionService
{
    /**
     * Repository اشتراک‌ها
     */
    protected SubscriptionRepositoryInterface $repository;

    public function __construct(
        SubscriptionRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * همه اشتراک‌ها
     */
    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * صفحه‌بندی اشتراک‌ها
     */
    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->repository->paginate(
            $perPage
        );
    }

    /**
     * یک اشتراک
     */
    public function findById(
        int $id
    ): ?Subscription
    {
        return $this->repository->findById(
            $id
        );
    }

    /**
     * اشتراک‌های کاربر
     */
    public function getByUser(
        int $userId
    ): Collection
    {
        return $this->repository->getByUser(
            $userId
        );
    }

    /**
     * اشتراک‌های فعال
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * اشتراک‌های منقضی شده
     */
    public function getExpired(): Collection
    {
        return $this->repository->getExpired();
    }

    /**
     * اشتراک‌های لغو شده
     */
    public function getCancelled(): Collection
    {
        return $this->repository->getCancelled();
    }

    /**
     * ایجاد اشتراک
     */
    public function create(
        array $data
    ): Subscription
    {
        try {

            return $this->repository->create(
                $data
            );

        } catch (Throwable $e) {

            Log::error('Subscription creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * بروزرسانی اشتراک
     */
    public function update(
        Subscription $subscription,
        array $data
    ): Subscription
    {
        try {

            return $this->repository->update(

                $subscription,

                $data

            );

        } catch (Throwable $e) {

            Log::error('Subscription update failed.', [

                'subscription_id' => $subscription->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * حذف اشتراک
     */
    public function delete(
        Subscription $subscription
    ): bool
    {
        try {

            return $this->repository->delete(
                $subscription
            );

        } catch (Throwable $e) {

            Log::error('Subscription delete failed.', [

                'subscription_id' => $subscription->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * فعال کردن اشتراک
     */
    public function activate(
        Subscription $subscription
    ): Subscription
    {
        try {

            return $this->repository->activate(
                $subscription
            );

        } catch (Throwable $e) {

            Log::error('Subscription activation failed.', [

                'subscription_id' => $subscription->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * لغو اشتراک
     */
    public function cancel(
        Subscription $subscription
    ): Subscription
    {
        try {

            return $this->repository->cancel(
                $subscription
            );

        } catch (Throwable $e) {

            Log::error('Subscription cancellation failed.', [

                'subscription_id' => $subscription->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * تمدید اشتراک
     */
    public function extend(
        Subscription $subscription,
        int $days
    ): Subscription
    {
        try {

            return $this->repository->extend(

                $subscription,

                $days

            );

        } catch (Throwable $e) {

            Log::error('Subscription extension failed.', [

                'subscription_id' => $subscription->id,

                'days' => $days,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }
}
