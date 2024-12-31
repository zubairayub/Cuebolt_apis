<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Firestore;

class GroupController extends Controller
{
    public function createGroup(Request $request)
    {
        $request->validate([
            'packageId' => 'required|string',
            'traderId' => 'required|string',
        ]);
    
       // $firestore = app('firebase.firestore');
       $firestore = app(Firestore::class)->database();
        $database = $firestore->database();
    
        $groupId = uniqid('group_');
        $groupData = [
            'packageId' => $request->packageId,
            'adminId' => $request->traderId,
            'members' => [$request->traderId],
            'createdAt' => now(),
        ];
    
        $database->collection('groups')->document($groupId)->set($groupData);
    
        return response()->json(['groupId' => $groupId]);
    }

    public function addMemberToGroup(Request $request)
{
    $request->validate([
        'groupId' => 'required|string',
        'buyerId' => 'required|string',
    ]);

    $firestore = app('firebase.firestore');
    $database = $firestore->database();

    $groupRef = $database->collection('groups')->document($request->groupId);
    $group = $groupRef->snapshot()->data();

    $members = $group['members'];
    $members[] = $request->buyerId;

    $groupRef->update([
        ['path' => 'members', 'value' => $members],
    ]);

    return response()->json(['message' => 'Buyer added to group']);
}

    
}
