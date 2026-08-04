<?php

namespace App\Services;

use App\Models\PdfFile;
use App\Repositories\Interfaces\PdfFileRepositoryInterface;

class PdfFileService
{
    protected PdfFileRepositoryInterface $pdfFileRepository;

    public function __construct(
        PdfFileRepositoryInterface $pdfFileRepository
    ) {
        $this->pdfFileRepository = $pdfFileRepository;
    }

    public function getAll()
    {
        return $this->pdfFileRepository->getAll();
    }

    public function findById(
        int $id
    ): ?PdfFile {
        return $this->pdfFileRepository->findById($id);
    }

    public function create(
        array $data
    ): PdfFile {
        return $this->pdfFileRepository->create($data);
    }

    public function update(
        PdfFile $pdfFile,
        array $data
    ): PdfFile {
        return $this->pdfFileRepository->update(
            $pdfFile,
            $data
        );
    }

    public function delete(
        PdfFile $pdfFile
    ): bool {
        return $this->pdfFileRepository->delete(
            $pdfFile
        );
    }
}
