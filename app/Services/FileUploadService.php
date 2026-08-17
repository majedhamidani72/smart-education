<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;
use Throwable;

class FileUploadService
{
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

    /*
    |--------------------------------------------------------------------------
    | Upload
    |--------------------------------------------------------------------------
    */

    public function upload(
        UploadedFile|TemporaryUploadedFile $file,
        string $folder
    ): array {

        $this->validateFolder($folder);

        try {

            $this->validateUploadedFile($file);

            $directory = $this->makeDirectory($folder);

            $extension = strtolower(
                $file->getClientOriginalExtension()
            );

            $filename = (string) Str::orderedUuid()
                . '.'
                . $extension;

            $targetDirectory = public_path($directory);

            if (! File::exists($targetDirectory)) {

                File::makeDirectory(
                    $targetDirectory,
                    0755,
                    true
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Livewire TemporaryUploadedFile
            |--------------------------------------------------------------------------
            */

            if ($file instanceof TemporaryUploadedFile) {

                File::copy(
                    $file->getRealPath(),
                    $targetDirectory
                        . DIRECTORY_SEPARATOR
                        . $filename
                );
            } else {

                $file->move(
                    $targetDirectory,
                    $filename
                );
            }

            $path = $targetDirectory
                . DIRECTORY_SEPARATOR
                . $filename;

            if (! File::exists($path)) {

                throw new RuntimeException(
                    'Uploaded file was not saved.'
                );
            }

            return [

                'directory' => $directory,

                'filename' => $filename,

                'original_name' => $file->getClientOriginalName(),

                'extension' => $extension,

                'mime_type' => File::mimeType($path),

                'file_size' => File::size($path),

            ];
        } catch (Throwable $e) {

            Log::error(
                'File upload failed.',
                [
                    'folder' => $folder,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Store From Path
    |--------------------------------------------------------------------------
    */

    public function storeFromPath(
        UploadedFile|TemporaryUploadedFile $file,
        string $folder
    ): array {

        return $this->upload(
            $file,
            $folder
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Replace
    |--------------------------------------------------------------------------
    */

    public function replaceFromPath(
        UploadedFile|TemporaryUploadedFile $file,
        ?string $directory,
        ?string $filename,
        string $folder
    ): array {

        $newFile = $this->upload(
            $file,
            $folder
        );

        $this->delete(
            $directory,
            $filename
        );

        return $newFile;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        ?string $directory,
        ?string $filename
    ): void {

        if (
            blank($directory)
            ||
            blank($filename)
        ) {

            return;
        }

        try {

            $path = $this->fullPath(
                $directory,
                $filename
            );

            if (File::exists($path)) {

                File::delete($path);
            }
        } catch (Throwable $e) {

            Log::error(
                'File delete failed.',
                [
                    'directory' => $directory,
                    'filename' => $filename,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateUploadedFile(
        UploadedFile|TemporaryUploadedFile $file
    ): void {

        $maxSize = config(
            'video.max_size',
            524288000
        );

        if (
            $file->getSize() > $maxSize
        ) {

            throw new RuntimeException(
                'File size is too large.'
            );
        }

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $allowed = config(
            'video.allowed_extensions',
            [
                'mp4',
                'webm',
                'mkv',
            ]
        );

        if (
            ! in_array(
                $extension,
                $allowed,
                true
            )
        ) {

            throw new RuntimeException(
                'Invalid file extension.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Directory
    |--------------------------------------------------------------------------
    */

    private function makeDirectory(
        string $folder
    ): string {

        $directory = sprintf(
            'uploads/%s/%s/%s',
            $folder,
            now()->format('Y'),
            now()->format('m')
        );

        $path = public_path($directory);

        if (! File::exists($path)) {

            File::makeDirectory(
                $path,
                0755,
                true
            );
        }

        if (! is_writable($path)) {

            throw new RuntimeException(
                'Upload directory is not writable: ' . $path
            );
        }

        return $directory;
    }

    /*
    |--------------------------------------------------------------------------
    | Folder Validation
    |--------------------------------------------------------------------------
    */

    private function validateFolder(
        string $folder
    ): void {

        if (
            ! in_array(
                $folder,
                $this->allowedFolders,
                true
            )
        ) {

            throw new InvalidArgumentException(
                'Upload folder is not allowed.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function fullPath(
        string $directory,
        string $filename
    ): string {

        return public_path(
            $directory
                . DIRECTORY_SEPARATOR
                . $filename
        );
    }

    public function exists(
        string $directory,
        string $filename
    ): bool {

        return File::exists(
            $this->fullPath(
                $directory,
                $filename
            )
        );
    }

    public function size(
        string $directory,
        string $filename
    ): ?int {

        $path = $this->fullPath(
            $directory,
            $filename
        );

        return File::exists($path)
            ? File::size($path)
            : null;
    }

    public function mimeType(
        string $directory,
        string $filename
    ): ?string {

        $path = $this->fullPath(
            $directory,
            $filename
        );

        return File::exists($path)
            ? File::mimeType($path)
            : null;
    }
}
