<?php

namespace App\Repositories\Interfaces;

use App\Models\TeacherEarning;

interface TeacherEarningRepositoryInterface
{
    public function create(array $data): TeacherEarning;
}
