<?php

namespace App\Repositories\Eloquent;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\QuizRepositoryInterface;

class QuizRepository extends BaseRepository implements QuizRepositoryInterface
{
    public function __construct(
        Quiz $quiz
    ) {
        parent::__construct($quiz);
    }


    public function getActiveQuizzes(): Collection
    {
        return $this->model
            ->newQuery()
            ->where('status', 'active')
            ->latest()
            ->get();
    }


    public function getWithQuestions(
        Quiz $quiz
    ): Quiz
    {
        return $quiz->load([
            'questions.options',
            'creator',
        ]);
    }
}
