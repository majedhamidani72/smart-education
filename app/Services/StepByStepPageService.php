<?php

namespace App\Services;

use Throwable;
use App\Models\StepByStepPage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\StepByStepPageRepositoryInterface;

class StepByStepPageService
{
    protected StepByStepPageRepositoryInterface $stepByStepPageRepository;

    protected FileUploadService $fileUploadService;

    public function __construct(
        StepByStepPageRepositoryInterface $stepByStepPageRepository,
        FileUploadService $fileUploadService
    ) {
        $this->stepByStepPageRepository = $stepByStepPageRepository;
        $this->fileUploadService = $fileUploadService;
    }

    public function getAll(): Collection
    {
        return $this->stepByStepPageRepository->getAll();
    }

    public function findById(
        int $id
    ): ?StepByStepPage {
        return $this->stepByStepPageRepository->findById($id);
    }

    public function create(
        array $data,
        ?UploadedFile $image
    ): StepByStepPage {

        DB::beginTransaction();

        try {

            if ($image) {

                $file = $this->fileUploadService->upload(
                    $image,
                    'step-by-step'
                );

                $data['image'] = $file['file_path'];
            }

            $page = $this->stepByStepPageRepository->create($data);

            DB::commit();

            return $page;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(
                'Create StepByStepPage Error',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }

    public function update(
        StepByStepPage $page,
        array $data,
        ?UploadedFile $image
    ): StepByStepPage {

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

            Log::error(
                'Update StepByStepPage Error',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }

    public function delete(
        StepByStepPage $page
    ): bool {

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

            Log::error(
                'Delete StepByStepPage Error',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }
}
