<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

abstract class BaseRepository
{
    /**
     * مدل Repository
     */
    protected Model $model;

    /**
     * سازنده
     */
    public function __construct(
        Model $model
    ) {
        $this->model = $model;
    }

    /**
     * دریافت همه رکوردها
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * دریافت یک رکورد
     */
    public function findById(
        int $id
    ): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * ایجاد رکورد
     */
    public function create(
        array $data
    ): Model
    {
        return $this->model->create($data);
    }

    /**
     * بروزرسانی رکورد
     */
    public function update(
        Model $model,
        array $data
    ): Model
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * حذف رکورد
     */
    public function delete(
        Model $model
    ): bool
    {
        return $model->delete();
    }
}
