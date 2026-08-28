<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PdfFile;
use App\Helpers\ApiResponse;
use App\Services\PdfFileService;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdfFileResource;
use App\Http\Requests\PdfFile\StorePdfFileRequest;
use App\Http\Requests\PdfFile\UpdatePdfFileRequest;

class PdfFileController extends Controller
{
    public function viewFile(PdfFile $pdfFile)
    {
        abort_unless(is_file($pdfFile->fullPath()), 404);

        return response()->file($pdfFile->fullPath(), [
            'Content-Type' => $pdfFile->mime_type ?: 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$pdfFile->filename.'"',
        ]);
    }

    /**
     * سرویس PDF
     */
    protected PdfFileService $pdfFileService;

    public function __construct(
        PdfFileService $pdfFileService
    ) {
        $this->pdfFileService = $pdfFileService;
    }

    /**
     * لیست PDF ها
     */
    public function index()
    {
        $this->authorize(
            'viewAny',
            PdfFile::class
        );

        $pdfFiles = $this->pdfFileService->paginate();

        return ApiResponse::success(

            PdfFileResource::collection(

                $pdfFiles

            ),

            'Pdf files retrieved successfully.'

        );
    }

    /**
     * نمایش یک PDF
     */
    public function show(
        PdfFile $pdfFile
    )
    {
        $this->authorize(
            'view',
            $pdfFile
        );

        return ApiResponse::success(

            new PdfFileResource(

                $pdfFile

            ),

            'Pdf file retrieved successfully.'

        );
    }

    /**
     * ایجاد PDF
     */
    public function store(
        StorePdfFileRequest $request
    )
    {
        $this->authorize(
            'create',
            PdfFile::class
        );

        $pdfFile = $this->pdfFileService->create(

            $request->validated()

        );

        return ApiResponse::success(

            new PdfFileResource(

                $pdfFile

            ),

            'Pdf file created successfully.',

            201

        );
    }

    /**
     * بروزرسانی PDF
     */
    public function update(
        UpdatePdfFileRequest $request,
        PdfFile $pdfFile
    )
    {
        $this->authorize(
            'update',
            $pdfFile
        );

        $pdfFile = $this->pdfFileService->update(

            $pdfFile,

            $request->validated()

        );

        return ApiResponse::success(

            new PdfFileResource(

                $pdfFile

            ),

            'Pdf file updated successfully.'

        );
    }

    /**
     * حذف PDF
     */
    public function destroy(
        PdfFile $pdfFile
    )
    {
        $this->authorize(
            'delete',
            $pdfFile
        );

        $this->pdfFileService->delete(

            $pdfFile

        );

        return ApiResponse::success(

            null,

            'Pdf file deleted successfully.'

        );
    }
}
