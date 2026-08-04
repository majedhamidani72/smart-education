<?php

namespace App\Repositories\Eloquent;

use App\Models\Chapter;
use App\Repositories\Interfaces\ChapterRepositoryInterface;

class ChapterRepository extends BaseRepository implements ChapterRepositoryInterface
{
    public function __construct(Chapter $model)
    {
        parent::__construct($model);
    }
}
