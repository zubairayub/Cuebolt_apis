<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\MulticastMessage;
use Kreait\Firebase\Messaging\MulticastSendReport;

class FirebaseServiceProvider extends ServiceProvider
{
    protected $messaging;

    public function __construct()
    {
        // Initialize Firebase Messaging
        $serviceAccountPath = storage_path('cuebolt-854b1-firebase-adminsdk-vmld7-7f5a214e83.json');
        $factory = (new Factory)->withServiceAccount($serviceAccountPath);
        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification(array $tokens, $title, $body, $data = [], $type)
    {
        // Split tokens into chunks of 500
        $tokenChunks = array_chunk($tokens, 500);

        foreach ($tokenChunks as $chunk) {
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            try {
                // Send a multicast notification to the current chunk
                $response = $this->messaging->sendMulticast($message, $chunk);

                // Log the notification details
                Log::info('Campaign Notification Sent', [
                    'Tokens' => $chunk,
                    'Title' => $title,
                    'Body' => $body,
                    'Data' => $data,
                    'Type' => $type,
                    'SuccessCount' => $response->successes()->count(),
                    'FailureCount' => $response->failures()->count(),
                ]);

                // Track each success and failure
                //$this->trackNotificationResponses($chunk, $response);

                // Log the campaign in Firebase Analytics
                $this->logNotificationToFirebaseAnalytics($chunk, $title, $body, $type);

            } catch (\Kreait\Firebase\Exception\MessagingException $e) {
                // Log the error for this chunk
                Log::error('Error sending campaign notification', [
                    'Tokens' => $chunk,
                    'Error' => $e->getMessage(),
                ]);
            }
        }
    }


    private function logNotificationToFirebaseAnalytics($token, $title, $body, $type)
    {
        // Firebase Analytics event logging
        try {
            $analytics = app('firebase.analytics');

            $analytics->logEvent('notification_sent', [
                'Token' => $token,
                'Title' => $title,
                'Body' => $body,
                'Type' => $type,
                'Timestamp' => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging to Firebase Analytics', [
                'Error' => $e->getMessage(),
            ]);
        }
    }

    // private function trackNotificationResponses(array $tokens, $response)
    // {
    //     foreach ($tokens as $index => $token) {
    //         if ($response->hasSuccess($index)) {
    //             Log::info("Notification delivered successfully", ['Token' => $token]);
    //         } elseif ($response->hasFailure($index)) {
    //             $failure = $response->failures()->get($index);
    //             Log::error("Notification delivery failed", [
    //                 'Token' => $token,
    //                 'Error' => $failure->rawErrorMessage(),
    //             ]);
    //         }
    //     }
    // }


    public function register()
    {
        // Registration logic if needed
    }

    public function boot()
    {
        // Boot logic if needed
    }
}
