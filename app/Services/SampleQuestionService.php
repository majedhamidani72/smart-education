<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SampleQuestion;
use App\Repositories\Interfaces\SampleQuestionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;
use Throwable;

class SampleQuestionService
{
    public function __construct(
        protected SampleQuestionRepositoryInterface $sampleQuestionRepository,
        protected FileUploadService $fileUploadService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Read
    |--------------------------------------------------------------------------
    */

    public function getAll(): Collection
    {
        return $this->sampleQuestionRepository->getAll();
    }

    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator {

        return $this->sampleQuestionRepository->paginate(
            $perPage
        );
    }

    public function findById(
        int $id
    ): ?SampleQuestion {

        return $this->sampleQuestionRepository->findById(
            $id
        );
    }

    public function pending(): Collection
    {
        return $this->sampleQuestionRepository->whereStatus(
            'pending'
        );
    }

    public function approved(): Collection
    {
        return $this->sampleQuestionRepository->whereStatus(
            'approved'
        );
    }

    public function rejected(): Collection
    {
        return $this->sampleQuestionRepository->whereStatus(
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
        UploadedFile|TemporaryUploadedFile $pdf
    ): SampleQuestion {

        DB::beginTransaction();

        try {

            $fileInfo = $this->fileUploadService->upload(

                $pdf,

                'sample-questions'

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

            $sampleQuestion = $this->sampleQuestionRepository->create(

                $data

            );

            DB::commit();

            return $sampleQuestion->fresh([

                'uploader',

                'approver',

                'contentItem.section.chapter.book',

            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'SampleQuestion creation failed.',

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
        SampleQuestion $sampleQuestion,
        array $data,
        UploadedFile|TemporaryUploadedFile|null $pdf = null
    ): SampleQuestion {

        DB::beginTransaction();

        try {

            if (

                $pdf instanceof UploadedFile

                ||

                $pdf instanceof TemporaryUploadedFile

            ) {

                $fileInfo = $this->fileUploadService->upload(

                    $pdf,

                    'sample-questions'

                );

                $this->fileUploadService->delete(

                    $sampleQuestion->directory,

                    $sampleQuestion->filename

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

            $sampleQuestion = $this->sampleQuestionRepository->update(

                $sampleQuestion,

                $data

            );

            DB::commit();

            return $sampleQuestion->fresh([

                'uploader',

                'approver',

                'contentItem.section.chapter.book',

            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'SampleQuestion update failed.',

                [

                    'sample_question_id' => $sampleQuestion->id,

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
        SampleQuestion $sampleQuestion
    ): SampleQuestion {

        DB::beginTransaction();

        try {

            $sampleQuestion = $this->sampleQuestionRepository->update(

                $sampleQuestion,

                [

                    'processing_status' => 'approved',

                    'approved_by' => Auth::id(),

                    'approved_at' => now(),

                    'rejected_reason' => null,

                ]

            );

            DB::commit();

            return $sampleQuestion->fresh([

                'uploader',

                'approver',

                'contentItem.section.chapter.book',

            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'SampleQuestion approval failed.',

                [

                    'sample_question_id' => $sampleQuestion->id,

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
        SampleQuestion $sampleQuestion,
        string $reason
    ): SampleQuestion {

        DB::beginTransaction();

        try {

            $sampleQuestion = $this->sampleQuestionRepository->update(

                $sampleQuestion,

                [

                    'processing_status' => 'rejected',

                    'approved_by' => Auth::id(),

                    'approved_at' => now(),

                    'rejected_reason' => $reason,

                ]

            );

            DB::commit();

            return $sampleQuestion->fresh([

                'uploader',

                'approver',

                'contentItem.section.chapter.book',

            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'SampleQuestion rejection failed.',

                [

                    'sample_question_id' => $sampleQuestion->id,

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
        SampleQuestion $sampleQuestion
    ): bool {

        DB::beginTransaction();

        try {

            $deleted = $this->sampleQuestionRepository->delete(

                $sampleQuestion

            );

            if ($deleted) {

                $this->fileUploadService->delete(

                    $sampleQuestion->directory,

                    $sampleQuestion->filename

                );

            }

            DB::commit();

            return $deleted;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'SampleQuestion delete failed.',

                [

                    'sample_question_id' => $sampleQuestion->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;

        }

    }
}
