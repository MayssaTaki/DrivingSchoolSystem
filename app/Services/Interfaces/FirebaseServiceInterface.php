<?php

namespace App\Services\Interfaces;


interface FirebaseServiceInterface
{

    public function getAccessToken(): string;
    public function sendNotification($deviceToken, $title, $body): array;
    
}
