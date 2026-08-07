<?php

namespace App\Services;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\PlanRepositoryInterface;

class PlanService
{
    /**
     * Repository
     */
    protected PlanRepositoryInterface $repository;

    /**
     * Constructor
     */
    public function __construct(
        PlanRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * لیست همه پلن‌ها
     */
    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * پلن‌های فعال
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * یک پلن
     */
    public function findById(
        int $id
    ): ?Plan
    {
        return $this->repository->findById($id);
    }

    /**
     * پلن‌های یک محصول
     */
    public function getByPlanable(
        string $type,
        int $id
    ): Collection
    {
        return $this->repository->getByPlanable(
            $type,
            $id
        );
    }

    /**
     * پلن‌های اشتراکی
     */
    public function getSubscriptions(): Collection
    {
        return $this->repository->getSubscriptions();
    }

    /**
     * پلن‌های خرید دائمی
     */
    public function getOneTimes(): Collection
    {
        return $this->repository->getOneTimes();
    }

    /**
     * ایجاد پلن
     */
    public function create(
        array $data
    ): Plan
    {
        return $this->repository->create(
            $data
        );
    }

    /**
     * بروزرسانی پلن
     */
    public function update(
        Plan $plan,
        array $data
    ): Plan
    {
        return $this->repository->update(
            $plan,
            $data
        );
    }

    /**
     * حذف پلن
     */
    public function delete(
        Plan $plan
    ): bool
    {
        return $this->repository->delete(
            $plan
        );
    }
}
