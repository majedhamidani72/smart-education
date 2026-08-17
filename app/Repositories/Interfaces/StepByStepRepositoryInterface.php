<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\StepByStep;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<StepByStep>
 */
interface StepByStepRepositoryInterface extends BaseRepositoryInterface
{
    public function whereStatus(
        string $status
    ): Collection;
}
