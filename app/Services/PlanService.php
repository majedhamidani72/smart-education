<?php

namespace App\Services;

use Throwable;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
     * صفحه‌بندی پلن‌ها
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
        return $this->repository->findById(
            $id
        );
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
        try {

            return $this->repository->create(
                $data
            );

        } catch (Throwable $e) {

            Log::error('Plan creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * بروزرسانی پلن
     */
    public function update(
        Plan $plan,
        array $data
    ): Plan
    {
        try {

            return $this->repository->update(

                $plan,

                $data

            );

        } catch (Throwable $e) {

            Log::error('Plan update failed.', [

                'plan_id' => $plan->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * حذف پلن
     */
    public function delete(
        Plan $plan
    ): bool
    {
        try {

            return $this->repository->delete(
                $plan
            );

        } catch (Throwable $e) {

            Log::error('Plan delete failed.', [

                'plan_id' => $plan->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }
}
