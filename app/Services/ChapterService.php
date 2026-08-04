<?php

namespace App\Services;

use App\Models\Chapter;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\ChapterRepositoryInterface;

class ChapterService
{
    protected ChapterRepositoryInterface $chapterRepository;

    public function __construct(ChapterRepositoryInterface $chapterRepository)
    {
        $this->chapterRepository = $chapterRepository;
    }

    public function getAll(): Collection
    {
        return $this->chapterRepository->getAll();
    }

    public function findById(int $id): ?Chapter
    {
        return $this->chapterRepository->findById($id);
    }

    public function create(array $data): Chapter
    {
        return $this->chapterRepository->create($data);
    }

    public function update(Chapter $chapter, array $data): Chapter
    {
        return $this->chapterRepository->update($chapter, $data);
    }

    public function delete(Chapter $chapter): bool
    {
        return $this->chapterRepository->delete($chapter);
    }
}
