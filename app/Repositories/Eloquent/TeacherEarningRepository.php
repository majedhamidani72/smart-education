<?php

namespace App\Repositories\Eloquent;

use App\Models\TeacherEarning;
use App\Repositories\Interfaces\TeacherEarningRepositoryInterface;

class TeacherEarningRepository implements TeacherEarningRepositoryInterface
{
    public function create(array $data): TeacherEarning
    {
        return TeacherEarning::create($data);
    }
}
