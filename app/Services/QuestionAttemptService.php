<?php

namespace App\Services;

use App\Models\QuestionAttempt;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\QuestionAttemptRepositoryInterface;

class QuestionAttemptService
{
    protected QuestionAttemptRepositoryInterface $questionAttemptRepository;

    public function __construct(
        QuestionAttemptRepositoryInterface $questionAttemptRepository
    ) {
        $this->questionAttemptRepository = $questionAttemptRepository;
    }

    public function getAll(): Collection
    {
        return $this->questionAttemptRepository->getAll();
    }

    public function findById(
        int $id
    ): ?QuestionAttempt {

        return $this->questionAttemptRepository->findById($id);
    }

    public function create(
        array $data
    ): QuestionAttempt {

        return $this->questionAttemptRepository->create($data);
    }

    public function update(
        QuestionAttempt $questionAttempt,
        array $data
    ): QuestionAttempt {

        return $this->questionAttemptRepository->update(
            $questionAttempt,
            $data
        );
    }

    public function delete(
        QuestionAttempt $questionAttempt
    ): bool {

        return $this->questionAttemptRepository->delete(
            $questionAttempt
        );
    }
}
