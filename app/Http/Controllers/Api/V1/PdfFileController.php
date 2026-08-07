<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PdfFile\StorePdfFileRequest;
use App\Http\Requests\PdfFile\UpdatePdfFileRequest;
use App\Http\Resources\PdfFileResource;
use App\Models\PdfFile;
use App\Services\FileUploadService;
use App\Services\PdfFileService;

class PdfFileController extends Controller
{
    // سرویس PDF
    protected PdfFileService $pdfFileService;

    // سرویس مدیریت فایل
    protected FileUploadService $fileUploadService;

    public function __construct(
        PdfFileService $pdfFileService,
        FileUploadService $fileUploadService
    ) {
        $this->pdfFileService = $pdfFileService;
        $this->fileUploadService = $fileUploadService;
    }

    // لیست PDF ها
    public function index()
    {
        return ApiResponse::success(
            PdfFileResource::collection(
                $this->pdfFileService->getAll()
            ),
            'Pdf files retrieved successfully.'
        );
    }

    // نمایش یک PDF
    public function show(PdfFile $pdfFile)
    {
        return ApiResponse::success(
            new PdfFileResource($pdfFile),
            'Pdf file retrieved successfully.'
        );
    }

    // ایجاد PDF
    public function store(StorePdfFileRequest $request)
    {
        $fileInfo = $this->fileUploadService->upload(
            $request->file('pdf'),
            'pdfs'
        );

        $data = $request->validated();

        $data['file'] = $fileInfo['file_path'];
        $data['file_size'] = $fileInfo['file_size'];

        $pdfFile = $this->pdfFileService->create($data);

        return ApiResponse::success(
            new PdfFileResource($pdfFile),
            'Pdf file created successfully.',
            201
        );
    }

    // بروزرسانی PDF
    public function update(
        UpdatePdfFileRequest $request,
        PdfFile $pdfFile
    ) {
        $data = $request->validated();

        if ($request->hasFile('pdf')) {

            $fileInfo = $this->fileUploadService->replace(
                $request->file('pdf'),
                $pdfFile->file,
                'pdfs'
            );

            $data['file'] = $fileInfo['file_path'];
            $data['file_size'] = $fileInfo['file_size'];
        }

        $pdfFile = $this->pdfFileService->update(
            $pdfFile,
            $data
        );

        return ApiResponse::success(
            new PdfFileResource($pdfFile),
            'Pdf file updated successfully.'
        );
    }

    // حذف PDF
    public function destroy(PdfFile $pdfFile)
    {
        $this->fileUploadService->delete(
            $pdfFile->file
        );

        $this->pdfFileService->delete($pdfFile);

        return ApiResponse::success(
            null,
            'Pdf file deleted successfully.'
        );
    }
}
