<?php

namespace App\Repositories\Interfaces;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface QuizRepositoryInterface extends BaseRepositoryInterface
{
    public function getAll(): Collection;

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


    public function getActiveQuizzes(): Collection;


    public function getWithQuestions(
        Quiz $quiz
    ): Quiz;
}
