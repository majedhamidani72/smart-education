<?php

namespace App\Repositories\Eloquent;

use App\Models\Quiz;
use App\Repositories\Interfaces\QuizRepositoryInterface;

class QuizRepository extends BaseRepository implements QuizRepositoryInterface
{
    public function __construct(
        Quiz $quiz
    ) {
        parent::__construct($quiz);
    }
}
