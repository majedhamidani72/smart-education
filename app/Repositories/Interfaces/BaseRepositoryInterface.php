<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * دریافت تمام رکوردها
     */
    public function getAll(): Collection;

    /**
     * دریافت یک رکورد بر اساس شناسه
     */
    public function findById(int $id): ?Model;

    /**
     * ایجاد رکورد جدید
     */
    public function create(array $data): Model;

    /**
     * بروزرسانی رکورد
     */
    public function update(Model $model, array $data): Model;

    /**
     * حذف نرم رکورد
     */
    public function delete(Model $model): bool;
}
