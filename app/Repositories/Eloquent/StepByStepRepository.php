<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\StepByStep;
use App\Repositories\Interfaces\StepByStepRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StepByStepRepository extends BaseRepository implements StepByStepRepositoryInterface
{
    public function __construct(
        StepByStep $model
    ) {
        parent::__construct($model);
    }

    public function getAll(): Collection
    {
        return $this->model
            ->with([
                'uploader',
                'approver',
                'contentItem.section.chapter.book',
            ])
            ->latest()
            ->get();
    }

    public function findById(
        int $id
    ): ?StepByStep {

        /** @var StepByStep|null */
        return $this->model
            ->with([
                'uploader',
                'approver',
                'contentItem.section.chapter.book',
            ])
            ->find($id);
    }

    public function whereStatus(
        string $status
    ): Collection {

        return $this->model
            ->with([
                'uploader',
                'approver',
                'contentItem.section.chapter.book',
            ])
            ->where(
                'processing_status',
                $status
            )
            ->latest()
            ->get();
    }
}
