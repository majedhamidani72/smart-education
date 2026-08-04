<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FileUploadService
{
    // پوشه‌های مجاز
    private array $allowedFolders = [
        'videos',
        'pdfs',
        'thumbnails',
        'step-by-step',
        'sample-questions',
        'advertisements',
        'profiles',
        'temp',
    ];

    // آپلود فایل
    public function upload(
        UploadedFile $file,
        string $folder
    ): array {

        // بررسی مجاز بودن پوشه
        if (!in_array($folder, $this->allowedFolders)) {
            throw new InvalidArgumentException(
                'Upload folder is not allowed.'
            );
        }

        $destinationPath = public_path(
            'uploads/' . $folder
        );

        // ایجاد پوشه در صورت نبودن
        if (!File::exists($destinationPath)) {
            File::makeDirectory(
                $destinationPath,
                0755,
                true
            );
        }

        // تولید نام یکتا
        $fileName = Str::uuid() . '.' .
            $file->getClientOriginalExtension();

        // انتقال فایل
        $file->move(
            $destinationPath,
            $fileName
        );

        // اطلاعات فایل
        return [
            'file_path' => 'uploads/' . $folder . '/' . $fileName,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    // حذف فایل
    public function delete(?string $filePath): void
    {
        if (!$filePath) {
            return;
        }

        $fullPath = public_path($filePath);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    // جایگزینی فایل
    public function replace(
        UploadedFile $file,
        ?string $oldFile,
        string $folder
    ): array {

        $this->delete($oldFile);

        return $this->upload(
            $file,
            $folder
        );
    }
}
