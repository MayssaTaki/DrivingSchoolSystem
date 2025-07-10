<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends Model
{
        protected $fillable = ['type', 'notifiable', 'data','read_at'];

    
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'data' => 'array',         
        'read_at' => 'datetime',  
    ];

    /**
     * Disable incrementing since id is UUID.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

}
