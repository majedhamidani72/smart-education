<?php

namespace App\Repositories\Eloquent;

use App\Models\QuestionOption;
use App\Repositories\Interfaces\QuestionOptionRepositoryInterface;

class QuestionOptionRepository extends BaseRepository implements QuestionOptionRepositoryInterface
{
    public function __construct(
        QuestionOption $questionOption
    ) {
        parent::__construct($questionOption);
    }
}
