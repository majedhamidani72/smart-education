<?php

namespace App\Repositories\Eloquent;

use App\Models\QuizAttempt;
use App\Repositories\Interfaces\QuizAttemptRepositoryInterface;

class QuizAttemptRepository extends BaseRepository implements QuizAttemptRepositoryInterface
{
    public function __construct(
        QuizAttempt $quizAttempt
    ) {
        parent::__construct($quizAttempt);
    }
}
