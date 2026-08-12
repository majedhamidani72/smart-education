<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected Model $model;


    public function __construct(
        Model $model
    ) {
        $this->model = $model;
    }


    public function getAll(): Collection
    {
        return $this->model
            ->newQuery()
            ->latest()
            ->get();
    }


    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator {

        return $this->model
            ->newQuery()
            ->latest()
            ->paginate($perPage);
    }


    public function query(): Builder
    {
        return $this->model
            ->newQuery();
    }


    public function findById(
        int $id
    ): ?Model {

        return $this->model
            ->newQuery()
            ->find($id);
    }


    public function create(
        array $data
    ): Model {

        return $this->model
            ->newQuery()
            ->create($data);
    }


    public function update(
        Model $model,
        array $data
    ): Model {

        $model->update($data);

        return $model->fresh();
    }


    public function delete(
        Model $model
    ): bool {

        return (bool) $model->delete();
    }
}
