<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Messaging;
use Illuminate\Support\Facades\Log;
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

    public function sendNotification($token, $title, $body, $data = [], $type)
    {
        // Build the message
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(['title' => $title, 'body' => $body])
            ->withData($data);

        try {
            // Send the message
            $this->messaging->send($message);

            Log::channel('notification_logs')->info('Firebase response:', [
                'Token' => $token,
                'Type' => $type,
            ]);


        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            // Handle Firebase messaging exception
            return response()->json([
                'message' => 'Error sending notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function register()
    {
        // No need to register manually
    }

    public function boot()
    {
        //
    }
}
