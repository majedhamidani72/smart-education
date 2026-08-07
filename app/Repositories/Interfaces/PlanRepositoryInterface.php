<?php

namespace App\Repositories\Interfaces;

interface PlanRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * پلن‌های فعال
     */
    public function getActive();

    /**
     * پلن بر اساس محصول
     */
    public function getByPlanable(
        string $type,
        int $id
    );

    /**
     * پلن‌های اشتراکی
     */
    public function getSubscriptions();

    /**
     * پلن‌های خرید دائمی
     */
    public function getOneTimes();
}
