<?php

namespace App\Services;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\QuizRepositoryInterface;

class QuizService
{
    protected QuizRepositoryInterface $quizRepository;

    public function __construct(
        QuizRepositoryInterface $quizRepository
    ) {
        $this->quizRepository = $quizRepository;
    }

    // دریافت همه آزمون‌ها
    public function getAll(): Collection
    {
        return $this->quizRepository->getAll();
    }

    // دریافت یک آزمون
    public function findById(
        int $id
    ): ?Quiz {
        return $this->quizRepository->findById($id);
    }

    // ایجاد آزمون
    public function create(
        array $data
    ): Quiz {
        return $this->quizRepository->create($data);
    }

    // بروزرسانی آزمون
    public function update(
        Quiz $quiz,
        array $data
    ): Quiz {

        return $this->quizRepository->update(
            $quiz,
            $data
        );
    }

    // حذف آزمون
    public function delete(
        Quiz $quiz
    ): bool {

        return $this->quizRepository->delete(
            $quiz
        );
    }
}
