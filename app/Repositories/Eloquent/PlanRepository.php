<?php

namespace App\Repositories\Eloquent;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\PlanRepositoryInterface;

class PlanRepository extends BaseRepository implements PlanRepositoryInterface
{
    public function __construct(
        Plan $model
    ) {
        parent::__construct($model);
    }

    /**
     * پلن‌های فعال
     */
    public function getActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->where(function ($query) {

                $query->whereNull('starts_at')
                    ->orWhere(
                        'starts_at',
                        '<=',
                        now()
                    );

            })
            ->where(function ($query) {

                $query->whereNull('expires_at')
                    ->orWhere(
                        'expires_at',
                        '>=',
                        now()
                    );

            })
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * پلن‌های یک محصول
     */
    public function getByPlanable(
        string $type,
        int $id
    ): Collection
    {
        return $this->model
            ->where(
                'planable_type',
                $type
            )
            ->where(
                'planable_id',
                $id
            )
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * پلن‌های اشتراکی
     */
    public function getSubscriptions(): Collection
    {
        return $this->model
            ->where(
                'purchase_type',
                'subscription'
            )
            ->where(
                'is_active',
                true
            )
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * پلن‌های خرید دائمی
     */
    public function getOneTimes(): Collection
    {
        return $this->model
            ->where(
                'purchase_type',
                'one_time'
            )
            ->where(
                'is_active',
                true
            )
            ->orderBy('sort_order')
            ->get();
    }
}
