<?php
namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImageUploaded
{
    use Dispatchable, SerializesModels;

    public string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}
