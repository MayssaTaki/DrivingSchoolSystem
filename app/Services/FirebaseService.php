<?php
namespace App\Services;

use Google\Client as GoogleClient;
use GuzzleHttp\Client;
use App\Services\Interfaces\LogServiceInterface;

class FirebaseService
{
    protected LogServiceInterface $logService;
 public function __construct(
    LogServiceInterface $logService
        )
    {
  
         $this->logService = $logService;

    }
    public function getAccessToken()
    {
        $client = new GoogleClient();
        $client->setAuthConfig(storage_path('app/firebase/service-account.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        return $client->fetchAccessTokenWithAssertion()['access_token'];
    }

    public function sendNotification($fcmToken, $title, $body, $data = [])
    {
        $accessToken = $this->getAccessToken();
        $projectId = 'driving-school-project-c7918';

        $client = new Client([
            'timeout' => 5, 
        ]);
         try {
        $response = $client->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data
                ],
            ],
        ]);

        return json_decode($response->getBody(), true);
    } catch (\Exception $e) {
            $this->logService->log('error', 'فشل fcm', [
                'message' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);                   throw $e;
}
    }}