<?php

namespace App\Repositories\Eloquent;

use App\Models\QuestionAttempt;
use App\Repositories\Interfaces\QuestionAttemptRepositoryInterface;

class QuestionAttemptRepository extends BaseRepository implements QuestionAttemptRepositoryInterface
{
    public function __construct(
        QuestionAttempt $questionAttempt
    ) {
        parent::__construct($questionAttempt);
    }
}
