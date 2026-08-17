<?php

namespace App\Repositories\Eloquent;

use App\Models\SampleQuestion;
use App\Repositories\Interfaces\SampleQuestionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SampleQuestionRepository extends BaseRepository implements SampleQuestionRepositoryInterface
{
    public function __construct(
        SampleQuestion $sampleQuestion
    ) {
        parent::__construct(
            $sampleQuestion
        );
    }

    public function whereStatus(
        string $status
    ): Collection {

        return $this->model

            ->where(
                'processing_status',
                $status
            )

            ->get();

    }
}
