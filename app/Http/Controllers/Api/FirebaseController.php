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
    // Validate the request
    $request->validate([
        'tokens' => 'required|array',  // Updated to accept an array of tokens
        'tokens.*' => 'string',         // Ensuring each token in the array is a string
        'title' => 'required|string',
        'body' => 'required|string',
        'data' => 'nullable|array',
    ]);

    // Extract the input values
    $tokens = $request->input('tokens');  // Tokens is now an array
    $title = $request->input('title');
    $body = $request->input('body');
    $data = $request->input('data');

    try {
        // Assuming your service method is sending the notification
        $type = "Testing";
        

        // Check if tokens exist and loop through them to send notifications
       
                send_push_notification($tokens, $title, $body, $data, $type);  // Sending notification for each token
           

        // Return success response
        return [
            'status' => 'success',
            'message' => 'Notification sent successfully'
        ];

    } catch (\Exception $e) {
        // Log the error if something goes wrong
        Log::error('Error sending notification', [
            'error_message' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString(),
        ]);
        return response()->json(['message' => 'Error sending notification', 'error' => $e->getMessage()], 500);
    }
}



    // {
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'title' => 'required|string',
    //         'body' => 'required|string',
    //     ]);

    //     $user = \App\Models\User::find($request->user_id);
    //     $fcm = $user->fcm_token;

    //     if (!$fcm) {
    //         return response()->json(['message' => 'User does not have a device token'], 400);
    //     }

    //     $title = $request->title;
    //     $description = $request->body;
    //     $projectId = config('services.fcm.project_id'); # INSERT COPIED PROJECT ID

    //     $credentialsFilePath = Storage::path('app/json/file.json');
    //     $client = new GoogleClient();
    //     $client->setAuthConfig($credentialsFilePath);
    //     $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    //     $client->refreshTokenWithAssertion();
    //     $token = $client->getAccessToken();

    //     $access_token = $token['access_token'];

    //     $headers = [
    //         "Authorization: Bearer $access_token",
    //         'Content-Type: application/json'
    //     ];

    //     $data = [
    //         "message" => [
    //             "token" => $fcm,
    //             "notification" => [
    //                 "title" => $title,
    //                 "body" => $description,
    //             ],
    //         ]
    //     ];
    //     $payload = json_encode($data);

    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    //     curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging
    //     $response = curl_exec($ch);
    //     $err = curl_error($ch);
    //     curl_close($ch);

    //     if ($err) {
    //         return response()->json([
    //             'message' => 'Curl Error: ' . $err
    //         ], 500);
    //     } else {
    //         return response()->json([
    //             'message' => 'Notification has been sent',
    //             'response' => json_decode($response, true)
    //         ]);
    //     }
    // }

}
// public function sendFcmNotification(Request $request)