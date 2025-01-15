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
use App\Models\UserProfile; // Import UserProfile model to get profile data

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

                Log::channel('notification_logs')->info('Notification sent successfully', [
                    'Token' => $chunk,
                    'Title' => $title,
                    'Body' => $body,
                    'Type' => $type,
                    'SuccessCount' => $response->successes()->count(),
                    'FailureCount' => $response->failures()->count(),
                ]);
            } catch (\Kreait\Firebase\Exception\MessagingException $e) {
                // Log the error for this chunk
                Log::error('Error sending notification', [
                    'Tokens' => $chunk,
                    'Error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function addUserToFirestore($userId,$username,$email,$profile_pictue)
    {
        try {
            // Retrieve user data from the User model (UserProfile model if needed)
            //$user = User::with('profile')->find($userId); // Assuming `profile` is a relation to user_profiles

            
           // $userProfile = $user->profile; // Fetch user profile (ensure `profile` relation exists)
            
            // Prepare data to write to Firestore
            $data = [
                'username' => $username,
                'user_id' => $userId,
                'email' => $email,
                'profile_picture_url' => $profile_pictue,
            ];

            // Get Firestore database instance
            $firestore = $this->firestore->database();
            
            // Reference to the 'users' collection and the specific document (userId)
            $docRef = $firestore->collection('users')->document($userId);

            // Log data before setting
            Log::channel('notification_logs')->info('Register user into firestore', ['data' => $data]);

            // Validate data
            // foreach ($data as $key => $value) {
            //     if (!is_string($value) && $value !== null) {
            //         Log::error("Invalid value for key '{$key}'", ['value' => $value]);
            //         throw new \InvalidArgumentException("Invalid value for key '{$key}'");
            //     }
            // }

            // Write to Firestore
            $result = $docRef->set($data);

            Log::info('Data successfully written to Firestore', [
                'document_path' => $docRef->path(), // `path()` is correct here
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
