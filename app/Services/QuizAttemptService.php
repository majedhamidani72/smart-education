<?php

namespace App\Services;

use App\Models\QuizAttempt;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\QuizAttemptRepositoryInterface;

class QuizAttemptService
{
    protected QuizAttemptRepositoryInterface $quizAttemptRepository;

    public function __construct(
        QuizAttemptRepositoryInterface $quizAttemptRepository
    ) {
        $this->quizAttemptRepository = $quizAttemptRepository;
    }

    public function getAll(): Collection
    {
        return $this->quizAttemptRepository->getAll();
    }

    public function findById(
        int $id
    ): ?QuizAttempt {

        return $this->quizAttemptRepository->findById($id);
    }

    public function create(
        array $data
    ): QuizAttempt {

        return $this->quizAttemptRepository->create($data);
    }

    public function update(
        QuizAttempt $quizAttempt,
        array $data
    ): QuizAttempt {

        return $this->quizAttemptRepository->update(
            $quizAttempt,
            $data
        );
    }

    public function delete(
        QuizAttempt $quizAttempt
    ): bool {

        return $this->quizAttemptRepository->delete(
            $quizAttempt
        );
    }
}
