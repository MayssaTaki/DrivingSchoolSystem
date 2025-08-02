<?php
namespace App\Models;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Model;

class PostFile extends Model
{
    protected $fillable = ['post_id', 'path', 'original_name', 'type'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

public function getUrlAttribute(): string
{
    $extension = strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));

    if (Storage::disk('public')->exists($this->path)) {
        return asset('storage/' . $this->path);
    }

    if (Str::startsWith($this->path, 'post_files/')) {
        return app(\App\Services\Interfaces\ImageServiceInterface::class)->getSignedUrl($this->path);
    }

   
}


}
