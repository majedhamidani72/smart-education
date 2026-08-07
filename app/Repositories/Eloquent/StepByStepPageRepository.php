<?php

namespace App\Repositories\Eloquent;

use App\Models\StepByStepPage;
use App\Repositories\Interfaces\StepByStepPageRepositoryInterface;

class StepByStepPageRepository extends BaseRepository implements StepByStepPageRepositoryInterface
{
    public function __construct(
        StepByStepPage $stepByStepPage
    ) {
        parent::__construct($stepByStepPage);
    }
}
