<?php

namespace App\Services;

use Throwable;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\QuestionOptionRepositoryInterface;

class QuestionOptionService
{
    protected QuestionOptionRepositoryInterface $questionOptionRepository;

    protected FileUploadService $fileUploadService;


    public function __construct(
        QuestionOptionRepositoryInterface $questionOptionRepository,
        FileUploadService $fileUploadService
    ) {
        $this->questionOptionRepository = $questionOptionRepository;
        $this->fileUploadService = $fileUploadService;
    }



    public function getAll(): Collection
    {
        return $this->questionOptionRepository->getAll();
    }



    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->questionOptionRepository->paginate($perPage);
    }



    public function findById(
        int $id
    ): ?QuestionOption
    {
        $questionOption = $this->questionOptionRepository->findById($id);


        if (!$questionOption) {
            return null;
        }


        return $this->loadRelations($questionOption);
    }



    public function loadRelations(
        QuestionOption $questionOption
    ): QuestionOption
    {
        return $questionOption->load([
            'question',
        ]);
    }



    public function create(
        array $data
    ): QuestionOption
    {
        try {

            return $this->questionOptionRepository->create($data);

        } catch (Throwable $e) {

            Log::error('Question option creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);


            throw $e;
        }
    }



    public function update(
        QuestionOption $questionOption,
        array $data
    ): QuestionOption
    {
        try {

            return $this->questionOptionRepository->update(
                $questionOption,
                $data
            );

        } catch (Throwable $e) {

            Log::error('Question option update failed.', [

                'question_option_id' => $questionOption->id,

                'error' => $e->getMessage(),

            ]);


            throw $e;
        }
    }



    public function delete(
        QuestionOption $questionOption
    ): bool
    {
        try {

            return $this->questionOptionRepository->delete(
                $questionOption
            );

        } catch (Throwable $e) {

            Log::error('Question option delete failed.', [

                'question_option_id' => $questionOption->id,

                'error' => $e->getMessage(),

            ]);


            throw $e;
        }
    }
}
