<?php

namespace App\Repositories\Interfaces;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Collection;

interface GradeRepositoryInterface
{
    /**
     * دریافت تمام پایه‌ها
     */
    public function getAll(): Collection;

    /**
     * دریافت پایه بر اساس شناسه
     */
    public function findById(int $id): ?Grade;

    /**
     * ایجاد پایه جدید
     */
    public function create(array $data): Grade;

    /**
     * بروزرسانی پایه
     */
    public function update(Grade $grade, array $data): Grade;

    /**
     * حذف نرم پایه
     */
    public function delete(Grade $grade): bool;
}
