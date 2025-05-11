<?php

use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Spatie\ImageOptimizer\Optimizers\Gifsicle;

return [

    'binary_path' => storage_path('app/tools'),

    'optimizers' => [
        Jpegoptim::class => ['--max=85', '--strip-all', '--all-progressive'],
        Pngquant::class  => ['--quality=85', '--force'],
        Optipng::class   => ['-i0', '-o2', '-quiet'],
        Gifsicle::class  => ['-b', '-O3'],
    ],

    'timeout' => 60,

    'log_optimizer_activity' => true,
];
