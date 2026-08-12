<?php

namespace App\Repositories\Interfaces;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

interface PlanRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * پلن‌های فعال
     */
    public function getActive(): Collection;

    /**
     * پلن بر اساس محصول
     */
    public function getByPlanable(
        string $type,
        int $id
    ): Collection;

    /**
     * پلن‌های اشتراکی
     */
    public function getSubscriptions(): Collection;

    /**
     * پلن‌های خرید دائمی
     */
    public function getOneTimes(): Collection;
}
