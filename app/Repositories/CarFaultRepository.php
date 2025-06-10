<?php

namespace App\Repositories;

use App\Models\CarFault;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\Cache;
use App\Repositories\Contracts\CarFaultRepositoryInterface;

class CarFaultRepository implements CarFaultRepositoryInterface
{
    public function create(array $data)
    {
        return CarFault::create($data);
    }


}