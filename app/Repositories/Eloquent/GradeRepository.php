<?php

namespace App\Repositories\Eloquent;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\GradeRepositoryInterface;


class GradeRepository implements GradeRepositoryInterface
{
    /**
     * دریافت تمام پایه‌ها
     */
    public function getAll(): Collection
    {
        return Grade::all();
    }

    /**
     * دریافت یک پایه با شناسه
     */
    public function findById(int $id): ?Grade
    {
        return Grade::findOrFail($id);    }

    /**
     * ایجاد پایه جدید
     */
    public function create(array $data): Grade
    {
        return Grade::create($data);
    }

    /**
     * بروزرسانی پایه
     */
    public function update(Grade $grade, array $data): Grade
    {
        $grade->update($data);

        return $grade->fresh();
    }

    /**
     * حذف نرم پایه
     */
    public function delete(Grade $grade): bool
    {
        return $grade->delete();
    }
}
