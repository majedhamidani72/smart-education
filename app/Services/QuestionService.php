<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\QuestionRepositoryInterface;

class QuestionService
{
    protected QuestionRepositoryInterface $questionRepository;

    public function __construct(
        QuestionRepositoryInterface $questionRepository
    ) {
        $this->questionRepository = $questionRepository;
    }

    // دریافت همه سوالات
    public function getAll(): Collection
    {
        return $this->questionRepository->getAll();
    }

    // دریافت یک سوال
    public function findById(
        int $id
    ): ?Question {
        return $this->questionRepository->findById($id);
    }

    // ایجاد سوال
    public function create(
        array $data
    ): Question {

        return $this->questionRepository->create(
            $data
        );
    }

    // بروزرسانی سوال
    public function update(
        Question $question,
        array $data
    ): Question {

        return $this->questionRepository->update(
            $question,
            $data
        );
    }

    // حذف سوال
    public function delete(
        Question $question
    ): bool {

        return $this->questionRepository->delete(
            $question
        );
    }
}
