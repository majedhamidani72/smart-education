<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;


interface TeacherAssignmentRepositoryInterface extends BaseRepositoryInterface
{

    /**
     * دریافت همه دسترسی‌ها
     */
    public function getAll(): Collection;



    /**
     * صفحه‌بندی
     */
    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator;



    /**
     * پیدا کردن بر اساس ID
     */
    public function findById(
        int $id
    ): ?Model;



    /**
     * ایجاد
     */
    public function create(
        array $data
    ): Model;



    /**
     * بروزرسانی
     */
    public function update(
        Model $model,
        array $data
    ): Model;



    /**
     * حذف
     */
    public function delete(
        Model $model
    ): bool;



    /**
     * دسترسی‌های یک معلم
     */
    public function getByTeacher(
        int $teacherId
    ): Collection;



    /**
     * دسترسی‌های یک کتاب
     */
    public function getByBook(
        int $bookId
    ): Collection;

}
