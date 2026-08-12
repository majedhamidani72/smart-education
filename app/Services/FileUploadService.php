<?php

declare(strict_types=1);

namespace App\Services;

use Throwable;
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







    /**
     * آپلود مستقیم فایل
     */
    public function upload(
        UploadedFile $file,
        string $folder
    ): array {


        $this->validateFolder($folder);



        try {


            $directory = $this->makeDirectory(
                $folder
            );



            $destination = public_path(
                $directory
            );



            $extension = strtolower(

                $file->getClientOriginalExtension()

            );



            $filename = Str::orderedUuid()
                . '.'
                . $extension;



            $fileSize = $file->getSize();



            $mimeType = $file->getMimeType();



            $originalName = $file->getClientOriginalName();




            $file->move(

                $destination,

                $filename

            );




            return [

                'directory' => $directory,

                'filename' => $filename,

                'original_name' => $originalName,

                'extension' => $extension,

                'mime_type' => $mimeType,

                'file_size' => $fileSize,

            ];
        } catch (Throwable $e) {


            Log::error(

                'File upload failed.',

                [

                    'error' => $e->getMessage(),

                ]

            );



            throw $e;
        }
    }







    /**
     * انتقال فایل موقت Filament به مسیر اصلی
     */
    public function storeFromPath(
        string $path,
        string $folder
    ): array {



        $this->validateFolder($folder);




        try {



            $source = storage_path(

                'app/' . ltrim($path, '/')

            );




            if (! File::exists($source)) {


                throw new \RuntimeException(

                    'Temporary uploaded file not found: ' . $source

                );
            }





            $directory = $this->makeDirectory(

                $folder

            );




            $destination = public_path(

                $directory

            );




            $extension = strtolower(

                File::extension($source)

            );




            $filename = Str::orderedUuid()

                . '.'

                . $extension;




            $target = $destination

                . DIRECTORY_SEPARATOR

                . $filename;





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

                'Store temp file failed.',

                [

                    'path' => $path,

                    'error' => $e->getMessage(),

                ]

            );



            throw $e;
        }
    }
    /**
     * جایگزینی فایل Filament
     */
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







    /**
     * حذف فایل
     */
    public function delete(
        ?string $directory,
        ?string $filename
    ): void {


        if (! $directory || ! $filename) {

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







    /**
     * ساخت مسیر ذخیره
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







    /**
     * بررسی پوشه مجاز
     */
    private function validateFolder(
        string $folder
    ): void {



        if (! in_array(

            $folder,

            $this->allowedFolders,

            true

        )) {



            throw new InvalidArgumentException(

                'Upload folder is not allowed.'

            );
        }
    }







    /**
     * مسیر کامل فایل
     */
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
