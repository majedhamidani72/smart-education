<?php

declare(strict_types=1);

namespace App\Services;

use Throwable;
use RuntimeException;
use InvalidArgumentException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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


    public function upload(
        UploadedFile $file,
        string $folder
    ): array {

        $this->validateFolder($folder);


        try {

            $this->validateUploadedFile(
                $file
            );


            $directory = $this->makeDirectory(
                $folder
            );


            $extension = strtolower(
                $file->getClientOriginalExtension()
            );


            $filename = Str::orderedUuid()
                . '.'
                . $extension;


            $file->move(

                public_path($directory),

                $filename

            );


            $path = public_path(
                $directory . '/' . $filename
            );


            return [

                'directory' => $directory,

                'filename' => $filename,

                'original_name' =>
                $file->getClientOriginalName(),

                'extension' => $extension,

                'mime_type' =>
                File::mimeType($path),

                'file_size' =>
                File::size($path),

            ];
        } catch (Throwable $e) {


            Log::error(

                'File upload failed.',

                [

                    'error' =>
                    $e->getMessage(),

                ]

            );


            throw $e;
        }
    }



    public function storeFromPath(
        string $path,
        string $folder
    ): array {


        $this->validateFolder(
            $folder
        );


        try {


            $source = storage_path(

                'app/' . ltrim($path, '/')

            );



            if (! File::exists($source)) {


                throw new RuntimeException(

                    'Temporary file not found: ' . $source

                );
            }



            $this->validateFile(
                $source
            );



            $directory = $this->makeDirectory(
                $folder
            );



            $extension = strtolower(

                File::extension($source)

            );



            $filename = Str::orderedUuid()

                . '.'

                . $extension;



            $target = public_path(

                $directory . '/' . $filename

            );



            File::move(

                $source,

                $target

            );



            return [

                'directory' => $directory,

                'filename' => $filename,

                'original_name' => basename($path),

                'extension' => $extension,

                'mime_type' => File::mimeType($target),

                'file_size' => File::size($target),

            ];
        } catch (Throwable $e) {


            Log::error(

                'Store temporary file failed.',

                [

                    'path' => $path,

                    'error' => $e->getMessage(),

                ]

            );


            throw $e;
        }
    }
    public function replaceFromPath(
        string $path,
        ?string $directory,
        ?string $filename,
        string $folder
    ): array {

        $newFile = $this->storeFromPath(
            $path,
            $folder
        );


        $this->delete(
            $directory,
            $filename
        );


        return $newFile;
    }




    public function delete(
        ?string $directory,
        ?string $filename
    ): void {


        if (
            blank($directory) ||
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

                    'error' =>
                    $e->getMessage(),

                ]

            );


            throw $e;
        }
    }




    private function validateUploadedFile(
        UploadedFile $file
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




    private function validateFile(
        string $path
    ): void {


        $maxSize = config(

            'video.max_size',

            524288000

        );



        if (

            File::size($path) > $maxSize

        ) {


            throw new RuntimeException(

                'File size is too large.'

            );
        }



        $extension = strtolower(

            File::extension($path)

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




    private function makeDirectory(
        string $folder
    ): string {


        $directory = sprintf(

            'uploads/%s/%s/%s',

            $folder,

            now()->format('Y'),

            now()->format('m')

        );



        $path = public_path(
            $directory
        );



        if (! File::exists($path)) {


            File::makeDirectory(

                $path,

                0755,

                true

            );
        }



        return $directory;
    }




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




    public function fullPath(
        string $directory,
        string $filename
    ): string {


        return public_path(

            $directory

                . DIRECTORY_SEPARATOR .

                $filename

        );
    }
}
