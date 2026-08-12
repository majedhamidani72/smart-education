<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\VideoRepositoryInterface;

class VideoRepository extends BaseRepository implements VideoRepositoryInterface
{
    /**
     * Constructor
     */
    public function __construct(
        Video $model
    ) {
        parent::__construct($model);
    }


    /**
     * دریافت ویدئوها بر اساس وضعیت
     *
     * pending
     * approved
     * rejected
     */
    public function whereStatus(
        string $status
    ): Collection
    {
        return $this->model
            ->where(
                'processing_status',
                $status
            )
            ->latest()
            ->get();
    }
}
