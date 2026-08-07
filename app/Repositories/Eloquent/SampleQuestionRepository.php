<?php

namespace App\Repositories\Eloquent;

use App\Models\SampleQuestion;
use App\Repositories\Interfaces\SampleQuestionRepositoryInterface;

class SampleQuestionRepository extends BaseRepository implements SampleQuestionRepositoryInterface
{
    public function __construct(
        SampleQuestion $sampleQuestion
    ) {
        parent::__construct($sampleQuestion);
    }
}
