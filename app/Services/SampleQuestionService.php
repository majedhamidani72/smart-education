<?php

namespace App\Services;

use Throwable;
use App\Models\SampleQuestion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\SampleQuestionRepositoryInterface;

class SampleQuestionService
{
    /**
     * Repository نمونه سوالات
     */
    protected SampleQuestionRepositoryInterface $sampleQuestionRepository;

    /**
     * سرویس آپلود فایل
     */
    protected FileUploadService $fileUploadService;

    public function __construct(
        SampleQuestionRepositoryInterface $sampleQuestionRepository,
        FileUploadService $fileUploadService
    ) {
        $this->sampleQuestionRepository = $sampleQuestionRepository;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * دریافت همه نمونه سوالات
     */
    public function getAll(): Collection
    {
        return $this->sampleQuestionRepository->getAll();
    }

    /**
     * صفحه‌بندی نمونه سوالات
     */
    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->sampleQuestionRepository->paginate(
            $perPage
        );
    }

    /**
     * دریافت یک نمونه سوال
     */
    public function findById(
        int $id
    ): ?SampleQuestion
    {
        return $this->sampleQuestionRepository->findById(
            $id
        );
    }

    /**
     * ایجاد نمونه سوال
     */
    public function create(
        array $data,
        ?UploadedFile $pdf
    ): SampleQuestion
    {
        DB::beginTransaction();

        try {

            if ($pdf) {

                $file = $this->fileUploadService->upload(
                    $pdf,
                    'sample-questions'
                );

                $data['file'] = $file['file_path'];
            }

            $sampleQuestion = $this->sampleQuestionRepository->create(
                $data
            );

            DB::commit();

            return $sampleQuestion;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Sample question creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * بروزرسانی نمونه سوال
     */
    public function update(
        SampleQuestion $sampleQuestion,
        array $data,
        ?UploadedFile $pdf
    ): SampleQuestion
    {
        DB::beginTransaction();

        try {

            if ($pdf) {

                $file = $this->fileUploadService->replace(
                    $pdf,
                    $sampleQuestion->file,
                    'sample-questions'
                );

                $data['file'] = $file['file_path'];
            }

            $sampleQuestion = $this->sampleQuestionRepository->update(
                $sampleQuestion,
                $data
            );

            DB::commit();

            return $sampleQuestion;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Sample question update failed.', [

                'sample_question_id' => $sampleQuestion->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * حذف نمونه سوال
     */
    public function delete(
        SampleQuestion $sampleQuestion
    ): bool
    {
        DB::beginTransaction();

        try {

            $this->fileUploadService->delete(
                $sampleQuestion->file
            );

            $deleted = $this->sampleQuestionRepository->delete(
                $sampleQuestion
            );

            DB::commit();

            return $deleted;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Sample question delete failed.', [

                'sample_question_id' => $sampleQuestion->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }
}
