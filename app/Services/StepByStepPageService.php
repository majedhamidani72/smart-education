<?php

namespace App\Services;

use Throwable;
use App\Models\StepByStepPage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\StepByStepPageRepositoryInterface;

class StepByStepPageService
{
    /**
     * Repository صفحات گام به گام
     */
    protected StepByStepPageRepositoryInterface $stepByStepPageRepository;

    /**
     * سرویس آپلود فایل
     */
    protected FileUploadService $fileUploadService;

    public function __construct(
        StepByStepPageRepositoryInterface $stepByStepPageRepository,
        FileUploadService $fileUploadService
    ) {
        $this->stepByStepPageRepository = $stepByStepPageRepository;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * دریافت همه صفحات
     */
    public function getAll(): Collection
    {
        return $this->stepByStepPageRepository->getAll();
    }

    /**
     * صفحه‌بندی صفحات
     */
    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->stepByStepPageRepository->paginate(
            $perPage
        );
    }

    /**
     * دریافت یک صفحه
     */
    public function findById(
        int $id
    ): ?StepByStepPage
    {
        return $this->stepByStepPageRepository->findById(
            $id
        );
    }

    /**
     * ایجاد صفحه
     */
    public function create(
        array $data,
        ?UploadedFile $image
    ): StepByStepPage
    {
        DB::beginTransaction();

        try {

            if ($image) {

                $file = $this->fileUploadService->upload(
                    $image,
                    'step-by-step'
                );

                $data['image'] = $file['file_path'];
            }

            $page = $this->stepByStepPageRepository->create(
                $data
            );

            DB::commit();

            return $page;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Step by step page creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * بروزرسانی صفحه
     */
    public function update(
        StepByStepPage $page,
        array $data,
        ?UploadedFile $image
    ): StepByStepPage
    {
        DB::beginTransaction();

        try {

            if ($image) {

                $file = $this->fileUploadService->replace(
                    $image,
                    $page->image,
                    'step-by-step'
                );

                $data['image'] = $file['file_path'];
            }

            $page = $this->stepByStepPageRepository->update(
                $page,
                $data
            );

            DB::commit();

            return $page;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Step by step page update failed.', [

                'step_by_step_page_id' => $page->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * حذف صفحه
     */
    public function delete(
        StepByStepPage $page
    ): bool
    {
        DB::beginTransaction();

        try {

            $this->fileUploadService->delete(
                $page->image
            );

            $deleted = $this->stepByStepPageRepository->delete(
                $page
            );

            DB::commit();

            return $deleted;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Step by step page delete failed.', [

                'step_by_step_page_id' => $page->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }
}
