<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Firestore;
use Google\Cloud\Firestore\FirestoreClient;
use App\Models\User; // Import User model at the top

class FirebaseServiceProvider extends ServiceProvider
{
    protected $messaging;
    protected $auth;
    protected $firestore;
    protected $factory;

    public function __construct()
    {
        // Initialize Firebase Factory and Firestore once
        $this->factory = (new Factory)->withServiceAccount(storage_path('cuebolt-854b1-firebase-adminsdk-vmld7-7f5a214e83.json'));

        // Initialize Firebase Messaging
        $this->messaging = $this->factory->createMessaging();

        // Initialize Firestore client using FirestoreClient directly
        // $this->firestore = new FirestoreClient([
        //     'projectId' => 'cuebolt-854b1', // Provide your Firebase project ID here
        // ]);
    }

    // Send Notification Method with retry logic
    public function sendNotification(array $tokens, $title, $body, $data = [], $type)
    {
        $tokenChunks = array_chunk($tokens, 500); // Split tokens into 500 chunks
    
        foreach ($tokenChunks as $chunk) {
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $attempts = 0;
            $maxRetries = 3;
    
            while ($attempts < $maxRetries) {
                try {
                    $response = $this->messaging->sendMulticast($message, $chunk);

                    Log::channel('notification_logs')->info('Notification sent successfully', [
                        'Tokens' => $chunk,
                        'Title' => $title,
                        'Body' => $body,
                        'Type' => $type,
                        'SuccessCount' => $response->successes()->count(),
                        'FailureCount' => $response->failures()->count(),
                    ]);
                    break;
                } catch (\Kreait\Firebase\Exception\MessagingException $e) {
                    $attempts++;
                    Log::error('Error sending notification (attempt ' . $attempts . ')', [
                        'Tokens' => $chunk,
                        'Error' => $e->getMessage(),
                    ]);

                    if ($attempts >= $maxRetries) {
                        Log::critical('Notification sending failed after maximum retries', [
                            'Tokens' => $chunk,
                            'Error' => $e->getMessage(),
                        ]);
                    } else {
                        sleep(pow(2, $attempts)); // Exponential backoff
                    }
                } catch (\Exception $e) {
                    Log::critical('Unexpected error during notification sending', [
                        'Tokens' => $chunk,
                        'Error' => $e->getMessage(),
                    ]);
                    break;
                }
            }
        }
    }

    // Add User to Firestore Method
    public function addUserToFirestore($userId, $username, $email, $profilePicture)
    {
        try {
            // Prepare data to write to Firestore
            $data = [
                'username' => $username,
                'user_id' => $userId,
                'email' => $email,
                'profile_picture_url' => $profilePicture,
            ];

            // Get reference to the 'users' collection in Firestore
            $usersCollection = $this->firestore->collection('users');
            $docRef = $usersCollection->document($userId); // Document path using the user ID

            // Log before writing to Firestore
            Log::info('Registering user into Firestore', ['data' => $data]);

            // Write data to Firestore
            $docRef->set($data);

            Log::info('Data successfully written to Firestore', [
                'document_path' => $docRef->path(),
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
        // Custom registration logic (if any)
    }

    public function boot()
    {
        // Custom boot logic (if needed)
    }
}
