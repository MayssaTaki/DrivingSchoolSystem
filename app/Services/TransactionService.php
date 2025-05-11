<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Closure;
use Throwable;

class TransactionService
{
    
    public function run(Closure $callback)
    {
        return DB::transaction(function () use ($callback) {
            return $callback();
        });
    }
}
