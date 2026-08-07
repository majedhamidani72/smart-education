<?php

namespace App\Services;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;

class SubscriptionService
{
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
        return $this->repository->create(
            $data
        );
    }

    /**
     * بروزرسانی اشتراک
     */
    public function update(
        Subscription $subscription,
        array $data
    ): Subscription
    {
        return $this->repository->update(
            $subscription,
            $data
        );
    }

    /**
     * حذف اشتراک
     */
    public function delete(
        Subscription $subscription
    ): bool
    {
        return $this->repository->delete(
            $subscription
        );
    }

    /**
     * فعال کردن اشتراک
     */
    public function activate(
        Subscription $subscription
    ): Subscription
    {
        return $this->repository->activate(
            $subscription
        );
    }

    /**
     * لغو اشتراک
     */
    public function cancel(
        Subscription $subscription
    ): Subscription
    {
        return $this->repository->cancel(
            $subscription
        );
    }

    /**
     * تمدید اشتراک
     */
    public function extend(
        Subscription $subscription,
        int $days
    ): Subscription
    {
        return $this->repository->extend(
            $subscription,
            $days
        );
    }
}
