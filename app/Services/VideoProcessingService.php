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


        if (! $this->ffmpegPath || ! File::exists($this->ffmpegPath)) {

            throw new RuntimeException(
                'FFmpeg path is invalid.'
            );
        }


        if (! $this->ffprobePath || ! File::exists($this->ffprobePath)) {

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
                    'Video file not found: ' . $videoPath
                );
            }



            $video->update([

                'processing_status' => 'processing',

            ]);



            $duration = $this->getDuration(
                $videoPath
            );



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

                'rejected_reason' => $e->getMessage(),

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



        $process->setTimeout(120);


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

            'stream=height',

            '-of',

            'csv=s=x:p=0',

            $path,

        ]);



        $process->setTimeout(120);



        $process->run();



        if (! $process->isSuccessful()) {

            return null;
        }



        $height = (int) trim(

            $process->getOutput()

        );



        return match (true) {

            $height >= 2160 => '4K',

            $height >= 1080 => '1080p',

            $height >= 720 => '720p',

            $height >= 480 => '480p',

            $height >= 360 => '360p',

            default => null,
        };
    }







    public function generateThumbnail(
        Video $video
    ): string {


        $directory = $video->directory . '/thumbnails';



        $thumbnailDirectory = public_path(
            $directory
        );



        if (! File::exists($thumbnailDirectory)) {


            File::makeDirectory(

                $thumbnailDirectory,

                0755,

                true

            );
        }



        $filename = 'thumb_' . uniqid() . '.jpg';



        $output = $thumbnailDirectory . DIRECTORY_SEPARATOR . $filename;



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

            '-q:v',

            '2',

            $output,

        ]);



        $process->setTimeout(180);



        $process->run();



        if (! $process->isSuccessful()) {


            throw new RuntimeException(

                'Thumbnail generation failed: ' .

                    $process->getErrorOutput()

            );
        }



        return $directory . '/' . $filename;
    }
}
