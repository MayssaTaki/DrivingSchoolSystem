<?php

namespace App\Services\Interfaces;


interface FirebaseServiceInterface
{

 public function getAccessToken();
public function sendNotification($fcmToken, $title, $body, $data = []);

}
