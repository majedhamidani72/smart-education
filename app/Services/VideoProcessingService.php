<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Video;
use RuntimeException;
use Throwable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class VideoProcessingService
{
    protected string $ffmpegPath;

    protected string $ffprobePath;


    public function __construct()
    {
        $this->ffmpegPath = (string) config(
            'video.ffmpeg_path'
        );

        $this->ffprobePath = (string) config(
            'video.ffprobe_path'
        );


        if (
            ! $this->ffmpegPath ||
            ! File::exists($this->ffmpegPath)
        ) {

            throw new RuntimeException(
                'FFmpeg path is invalid.'
            );
        }


        if (
            ! $this->ffprobePath ||
            ! File::exists($this->ffprobePath)
        ) {

            throw new RuntimeException(
                'FFprobe path is invalid.'
            );
        }
    }



    public function process(
        Video $video
    ): Video {

        try {


            $videoPath = $video->fullPath();


            if (! File::exists($videoPath)) {

                throw new RuntimeException(
                    'Video file not found.'
                );
            }



            $video->update([

                'processing_status' => 'processing',

            ]);



            $this->validateFileSize(
                $videoPath
            );



            $duration = $this->getDuration(
                $videoPath
            );



            $optimizedPath = $this->optimizeVideo(
                $video
            );



            if ($optimizedPath) {


                File::delete(
                    $videoPath
                );


                File::move(
                    $optimizedPath,
                    $videoPath
                );
            }



            $quality = $this->getQuality(
                $videoPath
            );



            $thumbnail = $this->generateThumbnail(
                $video
            );



            $video->update([

                'duration' => $duration,

                'quality' => $quality,

                'thumbnail_path' => $thumbnail,

                'processing_status' => 'pending',

            ]);



            return $video->fresh();
        } catch (Throwable $e) {



            $video->update([

                'processing_status' => 'rejected',

                'rejected_reason' =>
                $e->getMessage(),

            ]);



            Log::error(

                'Video processing failed.',

                [

                    'video_id' => $video->id,

                    'error' => $e->getMessage(),

                ]

            );



            throw $e;
        }
    }





    private function validateFileSize(
        string $path
    ): void {


        $maxSize = (int) config(

            'video.max_size',

            524288000

        );



        if (
            File::size($path) > $maxSize
        ) {

            throw new RuntimeException(
                'Video size is too large.'
            );
        }
    }





    private function optimizeVideo(
        Video $video
    ): string {


        $input = $video->fullPath();


        $output = storage_path(

            'app/public/'
                .
                $video->directory
                .
                '/optimized_'
                .
                $video->filename

        );



        $process = new Process([

            $this->ffmpegPath,


            '-i',

            $input,


            '-vf',

            'scale=w=1280:h=720:force_original_aspect_ratio=decrease',


            '-c:v',

            'libx264',


            '-preset',

            'medium',


            '-crf',

            '28',


            '-pix_fmt',

            'yuv420p',


            '-c:a',

            'aac',


            '-b:a',

            '128k',


            '-movflags',

            '+faststart',


            '-y',


            $output,

        ]);



        $process->setTimeout(
            900
        );


        $process->run();



        if (! $process->isSuccessful()) {


            throw new RuntimeException(

                'Video optimization failed: ' .
                    $process->getErrorOutput()

            );
        }



        return $output;
    }
    public function getDuration(
        string $path
    ): int {


        $process = new Process([

            $this->ffprobePath,

            '-v',

            'error',

            '-show_entries',

            'format=duration',

            '-of',

            'default=noprint_wrappers=1:nokey=1',

            $path,

        ]);



        $process->setTimeout(
            120
        );


        $process->run();



        if (! $process->isSuccessful()) {


            throw new RuntimeException(
                'Could not detect video duration.'
            );
        }



        return (int) round(

            (float) trim(
                $process->getOutput()
            )

        );
    }






    public function getQuality(
        string $path
    ): ?string {


        $process = new Process([


            $this->ffprobePath,


            '-v',

            'error',



            '-select_streams',

            'v:0',



            '-show_entries',

            'stream=width,height',



            '-of',

            'csv=s=x:p=0',



            $path,

        ]);



        $process->setTimeout(
            120
        );



        $process->run();



        if (! $process->isSuccessful()) {

            return null;
        }



        $resolution = trim(

            $process->getOutput()

        );



        if (! $resolution) {

            return null;
        }



        [$width, $height] = array_map(

            'intval',

            explode(
                'x',
                $resolution
            )

        );



        return match (true) {


            $height >= 2160 => '4K',


            $height >= 1080 => '1080p',


            $height >= 720 => '720p',


            $height >= 480 => '480p',


            $height >= 360 => '360p',


            default =>
            $width . 'x' . $height,
        };
    }







    public function generateThumbnail(
        Video $video
    ): string {


        $directory =
            $video->directory
            .
            '/thumbnails';



        $thumbnailDirectory =
            storage_path(
                'app/public/' . $directory
            );



        if (! File::exists($thumbnailDirectory)) {


            File::makeDirectory(

                $thumbnailDirectory,

                0755,

                true

            );
        }



        $filename =
            'thumb_'
            .
            uniqid()
            .
            '.jpg';



        $output =
            $thumbnailDirectory
            .
            DIRECTORY_SEPARATOR
            .
            $filename;





        $process = new Process([


            $this->ffmpegPath,


            '-i',


            $video->fullPath(),



            '-ss',


            (string) config(

                'video.thumbnail_second',

                3

            ),



            '-frames:v',


            '1',



            '-vf',


            'scale=640:-2',



            '-q:v',


            '2',



            '-y',


            $output,


        ]);



        $process->setTimeout(
            180
        );



        $process->run();



        if (! $process->isSuccessful()) {


            throw new RuntimeException(

                'Thumbnail generation failed: '
                    .
                    $process->getErrorOutput()

            );
        }



        return $directory . '/' . $filename;
    }
}
