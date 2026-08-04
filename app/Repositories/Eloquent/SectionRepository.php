<?php

namespace App\Repositories\Eloquent;

use App\Models\Section;
use App\Repositories\Interfaces\SectionRepositoryInterface;

class SectionRepository extends BaseRepository implements SectionRepositoryInterface
{
    public function __construct(Section $model)
    {
        parent::__construct($model);
    }
}
