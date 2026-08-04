<?php

namespace App\Services;

use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\SectionRepositoryInterface;

class SectionService
{
    public function __construct(
        protected SectionRepositoryInterface $sectionRepository
    ) {
    }

    public function getAll(): Collection
    {
        return $this->sectionRepository->getAll();
    }

    public function findById(int $id): ?Section
    {
        return $this->sectionRepository->findById($id);
    }

    public function create(array $data): Section
    {
        return $this->sectionRepository->create($data);
    }

    public function update(Section $section, array $data): Section
    {
        return $this->sectionRepository->update($section, $data);
    }

    public function delete(Section $section): bool
    {
        return $this->sectionRepository->delete($section);
    }
}
