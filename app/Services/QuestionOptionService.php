<?php

namespace App\Services;

use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Collection;
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

    public function findById(
        int $id
    ): ?QuestionOption {

        return $this->questionOptionRepository->findById($id);
    }

    public function create(
        array $data
    ): QuestionOption {

        return $this->questionOptionRepository->create($data);
    }

    public function update(
        QuestionOption $questionOption,
        array $data
    ): QuestionOption {

        return $this->questionOptionRepository->update(
            $questionOption,
            $data
        );
    }

    public function delete(
        QuestionOption $questionOption
    ): bool {

        return $this->questionOptionRepository->delete(
            $questionOption
        );
    }
}
