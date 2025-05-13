<?php
return [
    'login' => [
        'max_attempts' => 5,
        'decay_seconds' => 60,
    ],
    'refresh' => [
        'max_attempts' => 5,
        'decay_seconds' => 60,
    ],
    // Add other contexts as needed
];