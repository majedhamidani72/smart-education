<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface BaseRepositoryInterface
{
    /**
     * @return Collection<int, TModel>
     */
    public function getAll(): Collection;

    /**
     * @return TModel|null
     */
    public function findById(int $id): ?Model;

    /**
     * @param array<string,mixed> $data
     * @return TModel
     */
    public function create(array $data): Model;

    /**
     * @param TModel $model
     * @param array<string,mixed> $data
     * @return TModel
     */
    public function update(
        Model $model,
        array $data
    ): Model;

    /**
     * @param TModel $model
     */
    public function delete(
        Model $model
    ): bool;
}
