<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;
use App\Services\Interfaces\LogServiceInterface;

class FirebaseService
{
    protected array $serviceAccount;
    protected string $projectId;
    protected LogServiceInterface $logService;

    public function __construct(LogServiceInterface $logService)
    {
$path = storage_path('app/private/driving_school_project_c7918_firebase_adminsdk_fbsvc_45122c7f99.json');

        if (!file_exists($path)) {
        throw new \Exception("🔴 الملف غير موجود: $path");
    }
        $this->serviceAccount = json_decode(file_get_contents($path), true);
        $this->projectId = $this->serviceAccount['project_id'];
        $this->logService = $logService;
    }

    public function getAccessToken(): string
    {
        $payload = [
            "iss" => $this->serviceAccount['client_email'],
            "sub" => $this->serviceAccount['client_email'],
            "aud" => "https://oauth2.googleapis.com/token",
            "iat" => time(),
            "exp" => time() + 3600,
            "scope" => "https://www.googleapis.com/auth/firebase.messaging"
        ];

        $jwt = JWT::encode($payload, $this->serviceAccount['private_key'], 'RS256');

        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            return $response->json()['access_token'] ?? '';
        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل الحصول على access token من Firebase', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function sendNotification($deviceToken, $title, $body): array
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
                    "message" => [
                        "token" => $deviceToken,
                        "notification" => [
                            "title" => $title,
                            "body" => $body,
                        ]
                    ]
                ]);

            return $response->json();
        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل إرسال إشعار FCM', [
                'message' => $e->getMessage(),
                'token' => $deviceToken,
                'title' => $title,
                'body' => $body,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
