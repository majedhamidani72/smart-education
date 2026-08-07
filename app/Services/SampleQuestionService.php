<?php

namespace App\Services;

use Throwable;
use App\Models\SampleQuestion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\SampleQuestionRepositoryInterface;

class SampleQuestionService
{
    protected SampleQuestionRepositoryInterface $sampleQuestionRepository;

    protected FileUploadService $fileUploadService;

    public function __construct(
        SampleQuestionRepositoryInterface $sampleQuestionRepository,
        FileUploadService $fileUploadService
    ) {
        $this->sampleQuestionRepository = $sampleQuestionRepository;
        $this->fileUploadService = $fileUploadService;
    }

    public function getAll(): Collection
    {
        return $this->sampleQuestionRepository->getAll();
    }

    public function findById(
        int $id
    ): ?SampleQuestion {
        return $this->sampleQuestionRepository->findById($id);
    }

    public function create(
        array $data,
        ?UploadedFile $pdf
    ): SampleQuestion {

        DB::beginTransaction();

        try {

            if ($pdf) {

                $file = $this->fileUploadService->upload(
                    $pdf,
                    'sample-questions'
                );

                $data['file'] = $file['file_path'];
            }

            $sampleQuestion = $this->sampleQuestionRepository->create($data);

            DB::commit();

            return $sampleQuestion;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(
                'Create SampleQuestion Error',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }

    public function update(
        SampleQuestion $sampleQuestion,
        array $data,
        ?UploadedFile $pdf
    ): SampleQuestion {

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

            Log::error(
                'Update SampleQuestion Error',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }

    public function delete(
        SampleQuestion $sampleQuestion
    ): bool {

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

            Log::error(
                'Delete SampleQuestion Error',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }
}
