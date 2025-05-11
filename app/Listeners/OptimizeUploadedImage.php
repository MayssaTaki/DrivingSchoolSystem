<?php
namespace App\Listeners;

use App\Events\ImageUploaded;
use App\Jobs\OptimizeImageJob;

class OptimizeUploadedImage
{
    public function handle(ImageUploaded $event): void
    {
        OptimizeImageJob::dispatch($event->path);
    }
}
