<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Providers\FirebaseServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FirebaseController extends Controller
{
    protected $firebaseServiceProvider;

    public function __construct(FirebaseServiceProvider $firebaseService)
    {
        $this->firebaseServiceProvider = $firebaseService;
    }

    public function sendPushNotification(Request $request)
    {
        // Log the incoming request data
        Log::info('Sending push notification', $request->all());

        // Validate the request
        $request->validate([
            'token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'nullable|array',
        ]);

        Log::info('Validation passed');

        // Extract the input values
        $token = $request->input('token');
        $title = $request->input('title');
        $body = $request->input('body');
        $data = $request->input('data');

        // Log the extracted data
        Log::info('Notification data', [
            'token' => $token,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        try {
            // Assuming your service method is sending the notification
           $data =  $this->firebaseServiceProvider->sendNotification($token, $title, $body, $data);
            Log::info('Notification sent successfully');
           
        } catch (\Exception $e) {
            // Log the error if something goes wrong
            Log::error('Error sending notification', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Error sending notification', 'error' => $e->getMessage()], 500);
        }

        // Return a success response
        return response()->json(['message' => 'Notification Sent Successfully']);
    }
}
