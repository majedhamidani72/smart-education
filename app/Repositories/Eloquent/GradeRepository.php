<?php

namespace App\Repositories\Eloquent;

use App\Models\Grade;
use App\Repositories\Interfaces\GradeRepositoryInterface;

class GradeRepository extends BaseRepository implements GradeRepositoryInterface
{
    public function __construct(Grade $model)
    {
        parent::__construct($model);
    }
}
