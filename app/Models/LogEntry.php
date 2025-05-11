<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogEntry extends Model
{
    protected $fillable = ['level', 'message', 'context', 'channel'];

    protected $casts = [
        'context' => 'array',
    ];
}
