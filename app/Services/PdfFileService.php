<?php

namespace App\Services;

use Throwable;
use App\Models\PdfFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\PdfFileRepositoryInterface;

class PdfFileService
{
    /**
     * Repository فایل‌های PDF
     */
    protected PdfFileRepositoryInterface $pdfFileRepository;

    public function __construct(
        PdfFileRepositoryInterface $pdfFileRepository
    ) {
        $this->pdfFileRepository = $pdfFileRepository;
    }

    /**
     * دریافت همه فایل‌های PDF
     */
    public function getAll(): Collection
    {
        return $this->pdfFileRepository->getAll();
    }

    /**
     * صفحه‌بندی فایل‌های PDF
     */
    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->pdfFileRepository->paginate(
            $perPage
        );
    }

    /**
     * دریافت یک فایل PDF
     */
    public function findById(
        int $id
    ): ?PdfFile
    {
        return $this->pdfFileRepository->findById(
            $id
        );
    }

    /**
     * ایجاد فایل PDF
     */
    public function create(
        array $data
    ): PdfFile
    {
        try {

            return $this->pdfFileRepository->create(
                $data
            );

        } catch (Throwable $e) {

            Log::error('PDF file creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * بروزرسانی فایل PDF
     */
    public function update(
        PdfFile $pdfFile,
        array $data
    ): PdfFile
    {
        try {

            return $this->pdfFileRepository->update(

                $pdfFile,

                $data

            );

        } catch (Throwable $e) {

            Log::error('PDF file update failed.', [

                'pdf_file_id' => $pdfFile->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * حذف فایل PDF
     */
    public function delete(
        PdfFile $pdfFile
    ): bool
    {
        try {

            return $this->pdfFileRepository->delete(
                $pdfFile
            );

        } catch (Throwable $e) {

            Log::error('PDF file delete failed.', [

                'pdf_file_id' => $pdfFile->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }
}
