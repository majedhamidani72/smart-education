<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface VideoRepositoryInterface extends BaseRepositoryInterface
{
    public function getAll(): Collection;


    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator;


    public function findById(
        int $id
    ): ?Model;


    public function create(
        array $data
    ): Model;


    public function update(
        Model $model,
        array $data
    ): Model;


    public function delete(
        Model $model
    ): bool;


    public function whereStatus(
        string $status
    ): Collection;
}
