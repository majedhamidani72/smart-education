<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\StepByStep;
use App\Repositories\Interfaces\StepByStepRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;
use Throwable;

class StepByStepService
{
    public function __construct(
        protected StepByStepRepositoryInterface $stepByStepRepository,
        protected FileUploadService $fileUploadService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Read
    |--------------------------------------------------------------------------
    */

    public function getAll(): Collection
    {
        return $this->stepByStepRepository->getAll();
    }

    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator {

        return $this->stepByStepRepository->paginate(
            $perPage
        );
    }

    public function findById(
        int $id
    ): ?StepByStep {

        return $this->stepByStepRepository->findById(
            $id
        );
    }

    public function pending(): Collection
    {
        return $this->stepByStepRepository->whereStatus(
            'pending'
        );
    }

    public function approved(): Collection
    {
        return $this->stepByStepRepository->whereStatus(
            'approved'
        );
    }

    public function rejected(): Collection
    {
        return $this->stepByStepRepository->whereStatus(
            'rejected'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(
        array $data,
        UploadedFile|TemporaryUploadedFile $image
    ): StepByStep {

        DB::beginTransaction();

        try {

            $fileInfo = $this->fileUploadService->upload(

                $image,

                'step-by-step'

            );

            $data = array_merge(

                $data,

                $fileInfo

            );

            $userId = Auth::id();

            if (! $userId) {

                throw new RuntimeException(
                    'Authenticated user not found.'
                );
            }

            $data['uploaded_by'] = $userId;

            $data['processing_status'] = 'pending';

            $data['download_allowed'] ??= false;

            $stepByStep = $this->stepByStepRepository->create(

                $data

            );

            DB::commit();

            return $stepByStep->fresh([

                'uploader',

                'approver',

                'contentItem.section.chapter.book',

            ]);
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'StepByStep creation failed.',

                [

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        StepByStep $stepByStep,
        array $data,
        UploadedFile|TemporaryUploadedFile|null $image = null
    ): StepByStep {

        DB::beginTransaction();

        try {

            if (
                $image instanceof UploadedFile
                ||
                $image instanceof TemporaryUploadedFile
            ) {

                $fileInfo = $this->fileUploadService->upload(

                    $image,

                    'step-by-step'

                );

                $this->fileUploadService->delete(

                    $stepByStep->directory,

                    $stepByStep->filename

                );

                $data = array_merge(

                    $data,

                    $fileInfo

                );

                $data['processing_status'] = 'pending';

                $data['approved_by'] = null;

                $data['approved_at'] = null;

                $data['rejected_reason'] = null;
            }

            $stepByStep = $this->stepByStepRepository->update(

                $stepByStep,

                $data

            );

            DB::commit();

            return $stepByStep->fresh([

                'uploader',

                'approver',

                'contentItem.section.chapter.book',

            ]);
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'StepByStep update failed.',

                [

                    'step_by_step_id' => $stepByStep->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        StepByStep $stepByStep
    ): StepByStep {

        DB::beginTransaction();

        try {

            $stepByStep = $this->stepByStepRepository->update(

                $stepByStep,

                [

                    'processing_status' => 'approved',

                    'approved_by' => Auth::id(),

                    'approved_at' => now(),

                    'rejected_reason' => null,

                ]

            );

            DB::commit();

            return $stepByStep->fresh([

                'uploader',

                'approver',

                'contentItem.section.chapter.book',

            ]);
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'StepByStep approval failed.',

                [

                    'step_by_step_id' => $stepByStep->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        StepByStep $stepByStep,
        string $reason
    ): StepByStep {

        DB::beginTransaction();

        try {

            $stepByStep = $this->stepByStepRepository->update(

                $stepByStep,

                [

                    'processing_status' => 'rejected',

                    'approved_by' => Auth::id(),

                    'approved_at' => now(),

                    'rejected_reason' => $reason,

                ]

            );

            DB::commit();

            return $stepByStep->fresh([

                'uploader',

                'approver',

                'contentItem.section.chapter.book',

            ]);
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'StepByStep rejection failed.',

                [

                    'step_by_step_id' => $stepByStep->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        StepByStep $stepByStep
    ): bool {

        DB::beginTransaction();

        try {

            $deleted = $this->stepByStepRepository->delete(

                $stepByStep

            );

            if ($deleted) {

                $this->fileUploadService->delete(

                    $stepByStep->directory,

                    $stepByStep->filename

                );
            }

            DB::commit();

            return $deleted;
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'StepByStep delete failed.',

                [

                    'step_by_step_id' => $stepByStep->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }
}
