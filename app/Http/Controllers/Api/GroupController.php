<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Exception\DatabaseException;
use App\Providers\FirebaseServiceProvider;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    protected $FirebaseServiceProvider;

    // Inject FirebaseServiceProvider to access Firebase services
    public function __construct(FirebaseServiceProvider $FirebaseServiceProvider)
    {
        $this->FirebaseServiceProvider = $FirebaseServiceProvider;
    }

    public function createGroup(Request $request)
    {
        // Validate incoming request parameters
        $request->validate([
            'packageId' => 'required|string',
            'traderId' => 'required|string',
        ]);

        // Generate a unique group ID
        $groupId = uniqid('group_');

        // Prepare the group data
        $groupData = [
            'packageId' => $request->packageId,
            'adminId' => $request->traderId,
            'members' => [$request->traderId], // Start with the trader as the admin/member
            'createdAt' => now(),
        ];

        try {
            // Get Firestore instance from FirebaseServiceProvider
            // $database = $this->FirebaseServiceProvider->getFirestoreDatabase();

            // // Create the group document in Firestore
            // $database->collection('groups')->document($groupId)->set($groupData);
            // Log::info('Group created successfully with ID: ' . $groupId);

            return response()->json(['groupId' => $groupId]);
        } catch (DatabaseException $e) {
            Log::error('Error creating group: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create group'], 500);
        }
    }
}

