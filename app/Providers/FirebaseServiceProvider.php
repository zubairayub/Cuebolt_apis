<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Firestore;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Util\JSON;
use App\Models\User; // Import User model at the top

class FirebaseServiceProvider extends ServiceProvider
{
    protected $messaging;
    protected $auth;
    protected $firestore;
    protected $factory;

    public function __construct()
    {
        // Initialize Firebase Factory with the service account
        $this->factory = (new Factory)->withServiceAccount(storage_path('cuebolt-854b1-firebase-adminsdk-vmld7-7f5a214e83.json'));
        
        $this->firestore = new FirestoreClient([
            'keyFilePath' => storage_path('cuebolt-854b1-firebase-adminsdk-vmld7-7f5a214e83.json')
        ]);
        // Initialize Firebase Messaging
        $this->messaging = $this->factory->createMessaging();

        // Initialize Firebase Auth
       // $this->auth = $this->factory->createAuth();

        // Initialize Firestore
        $this->firestore = $this->factory->createFirestore(); // Firestore initialization
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

                Log::channel('notification_logs')->info('Notification:', [
                    'Token' => $chunk,
                    'Title' => $title,
                    'Body' => $body,
                    'Type' => $type,
                    'SuccessCount' => $response->successes()->count(),
                    'FailureCount' => $response->failures()->count(),
                ]);
            } catch (\Kreait\Firebase\Exception\MessagingException $e) {
                // Log the error for this chunk
                Log::error('Error sending campaign notification', [
                    'Tokens' => $chunk,
                    'Error' => $e->getMessage(),
                ]);
            }
        }
    }

    // public function createUser($email, $password, $displayName = null)
    // {
    //     try {
    //         // Create a new user using Firebase Auth
    //         $user = $this->auth->createUser([
    //             'email' => $email,
    //             'password' => $password,
    //             'displayName' => $displayName,
    //         ]);

    //         // Log success
    //         Log::info('User created in Firebase', ['uid' => $user->uid, 'email' => $email]);

    //         return $user;
    //     } catch (AuthException $e) { // Catch Firebase Auth-specific exceptions
    //         Log::error('Error creating user in Firebase', ['error' => $e->getMessage()]);
    //         throw $e;
    //     } catch (FirebaseException $e) { // Catch general Firebase exceptions
    //         Log::error('General Firebase error', ['error' => $e->getMessage()]);
    //         throw $e;
    //     }
    // }

  

    public function addUserToFirestore($userId)
{
   
        // Dummy data to be written to Firestore
        $data = [
            'username' => 'John Doe',
            
            
        ];

        $firestore = $this->firestore->database();
        

        $docRef = $firestore->collection('users')->document('user_123'); 
        // Reference to the document in Firestore (replace with your collection/document path)
        $docRef = $this->firestore->collection('users')->document('user_123');

        try {
            // Log data before setting
            Log::info('Preparing to write data', ['data' => $data]);

            // Validate data
            foreach ($data as $key => $value) {
                if (!is_string($value) && $value !== '') {
                    Log::error("Invalid value for key '{$key}'", ['value' => $value]);
                    throw new \InvalidArgumentException("Invalid value for key '{$key}'");
                }
            }

            
           

            // Write to Firestore
            $result = $docRef->set($data);

            Log::info('Data successfully written to Firestore', [
                'document_path' => $docRef->name(),
                'data' => $data,
            ]);
        } catch (\Google\Cloud\Core\Exception\GoogleException $e) {
            Log::error('Firestore Google Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Generic Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    
    
    
}
    

    public function register()
    {
        // Registration logic if needed
    }

    public function boot()
    {
        // Boot logic if needed
    }
}
