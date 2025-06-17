<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Closure;
use Throwable;
use App\Services\Interfaces\TransactionServiceInterface;


class TransactionService  implements TransactionServiceInterface
{
    
    public function run(Closure $callback)
    {
        return DB::transaction(function () use ($callback) {
            return $callback();
        });
    }
}
