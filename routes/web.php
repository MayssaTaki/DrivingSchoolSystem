<?php

use Illuminate\Support\Facades\Route;
use App\Services\Interfaces\LogServiceInterface;

Route::get('/', function () {
    return view('welcome');
});
