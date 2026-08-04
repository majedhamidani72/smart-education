<?php

namespace App\Repositories\Eloquent;

use App\Models\ContentItem;
use App\Repositories\Interfaces\ContentItemRepositoryInterface;

class ContentItemRepository extends BaseRepository implements ContentItemRepositoryInterface
{
    public function __construct(ContentItem $model)
    {
        parent::__construct($model);
    }
}
