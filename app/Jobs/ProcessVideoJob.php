<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Video;
use App\Services\VideoProcessingService;
use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessVideoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    public int $tries = 3;


    public int $timeout = 300;



    public function __construct(
        public int $videoId
    ) {}



    public function handle(
        VideoProcessingService $videoProcessingService
    ): void {


        $video = Video::find(
            $this->videoId
        );



        if (! $video) {


            Log::warning(
                'Video not found for processing.',
                [
                    'video_id' => $this->videoId,
                ]
            );


            return;

        }




        try {


            $videoProcessingService->process(
                $video
            );


        } catch (Throwable $e) {


            Log::error(
                'Video processing error.',
                [
                    'video_id' => $this->videoId,
                    'error' => $e->getMessage(),
                ]
            );


            throw $e;

        }

    }




    public function failed(
        ?Throwable $exception
    ): void {


        $video = Video::find(
            $this->videoId
        );



        if ($video) {


            $video->update([

                'processing_status' => 'rejected',

                'rejected_reason' => $exception?->getMessage()
                    ?? 'Unknown processing error.',

            ]);

        }




        Log::error(
            'Video processing job permanently failed.',
            [
                'video_id' => $this->videoId,

                'error' => $exception?->getMessage(),

            ]
        );

    }
}
