<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\VideoRepositoryInterface;

class VideoRepository extends BaseRepository implements VideoRepositoryInterface
{
    public function __construct(
        Video $model
    ) {
        parent::__construct($model);
    }



    public function whereStatus(
        string $status
    ): Collection {

        return $this->model

            ->with([

                'contentItem.section.chapter.book',

                'uploader',

                'approver',

            ])

            ->where(
                'processing_status',
                $status
            )

            ->latest()

            ->get();
    }
}
