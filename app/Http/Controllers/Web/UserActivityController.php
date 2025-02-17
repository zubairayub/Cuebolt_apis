<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class UserActivityController extends Controller
{
    /**
     * Log when a user visits a screen.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logScreenVisit(Request $request)
    {
        // Validate incoming request parameters
        $validated = $request->validate([
            'screen' => 'required|string',
            'started_at' => 'required|date', // Timestamp when the screen visit started
        ]);

        try {
            // Get authenticated user
            $user = Auth::user();
            
            // Check if the user is authenticated
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Get the token (from Bearer token in Authorization header)
            $token = $request->bearerToken();

            // Validate token
            if (!$token) {
                return response()->json(['error' => 'Token missing'], 400);
            }

            // Log the screen visit activity
            log_user_activity($user->id, 'visit_screen', $token, $validated['screen']);

            return response()->json(['status' => 'success', 'message' => 'Screen visit logged successfully']);

        } catch (\Exception $e) {
            // Catch any errors during logging
            return response()->json(['error' => 'An error occurred', 'message' => $e->getMessage()], 500);
        }
    }

     /**
     * Log when a user exits a screen.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logScreenExit(Request $request)
    {
        // Validate incoming request parameters
        $validated = $request->validate([
            'screen' => 'required|string',
            'ended_at' => 'required|date', // Timestamp when the screen visit ended
        ]);

        try {
            // Get authenticated user
            $user = Auth::user();
            
            // Check if the user is authenticated
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Get the token (from Bearer token in Authorization header)
            $token = $request->bearerToken();

            // Validate token
            if (!$token) {
                return response()->json(['error' => 'Token missing'], 400);
            }

            // Log the screen exit activity
            log_user_activity($user->id, 'exit_screen', $token, $validated['screen'], $validated['ended_at']);

            return response()->json(['status' => 'success', 'message' => 'Screen exit logged successfully']);

        } catch (\Exception $e) {
            // Catch any errors during logging
            return response()->json(['error' => 'An error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Log when a user clicks a button.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logButtonClick(Request $request)
    {
        // Validate incoming request parameters
        $validated = $request->validate([
            'button' => 'required|string',
            'clicked_at' => 'required|date', // Timestamp when the button click happened
        ]);

        try {
            // Get authenticated user
            $user = Auth::user();
            
            // Check if the user is authenticated
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Get the token (from Bearer token in Authorization header)
            $token = $request->bearerToken();

            // Validate token
            if (!$token) {
                return response()->json(['error' => 'Token missing'], 400);
            }

            // Log the button click activity
            log_user_activity($user->id, 'click_button', $token, $validated['button'], $validated['clicked_at']);

            return response()->json(['status' => 'success', 'message' => 'Button click logged successfully']);

        } catch (\Exception $e) {
            // Catch any errors during logging
            return response()->json(['error' => 'An error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Log multiple user activities in a batch.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logBatchActivities(Request $request)
    {
        // Validate incoming request data
        $validated = $request->validate([
            'activities' => 'required|array',
            'activities.*.action' => 'required|string|in:login,visit_screen,exit_screen,click_button', // Valid actions
            'activities.*.screen' => 'nullable|string', // Optional for screen actions
            'activities.*.button' => 'nullable|string', // Optional for button actions
            'activities.*.started_at' => 'nullable|date', // Timestamp for when the activity started
            'activities.*.ended_at' => 'nullable|date', // Timestamp for when the activity ended
            'activities.*.clicked_at' => 'nullable|date', // Timestamp for button click
        ]);

        // Get authenticated user
        $user = Auth::user();
        
        // Check if the user is authenticated
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Get the token (from Bearer token in Authorization header)
        $token = $request->bearerToken();

        // Validate token
        if (!$token) {
            return response()->json(['error' => 'Token missing'], 400);
        }

        $loggedActivities = [];
        $errors = [];

        // Iterate through the activities and log each one
        foreach ($validated['activities'] as $activityData) {
            try {
                // Determine the action type and log the activity accordingly
                switch ($activityData['action']) {
                    case 'login':
                        log_user_activity($user->id, 'login', $token, null, $activityData['started_at']);
                        break;

                    case 'visit_screen':
                        log_user_activity($user->id, 'visit_screen', $token, $activityData['screen'], $activityData['started_at']);
                        break;

                    case 'exit_screen':
                        log_user_activity($user->id, 'exit_screen', $token, $activityData['screen'], $activityData['ended_at']);
                        break;

                    case 'click_button':
                        log_user_activity($user->id, 'click_button', $token, $activityData['button'], $activityData['clicked_at']);
                        break;

                    default:
                        throw new \Exception('Invalid action type');
                }

                $loggedActivities[] = $activityData; // Add to logged activities list

            } catch (\Exception $e) {
                $errors[] = [
                    'activity' => $activityData,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Return response with logged activities and errors
        return response()->json([
            'status' => 'success',
            'logged_activities' => $loggedActivities,
            'errors' => $errors
        ]);
    }

        // Method to log user login activity
        public function logLogin(Request $request)
        {
            
            // Validate the incoming request parameters
            $validated = $request->validate([
                'action' => 'required|string',  // Auth token
                'started_at' => 'required|date',  // Timestamp when login started
            ]);
            
            

            try {
                // Get the authenticated user using the token
                $user = Auth::user();
        
                // Check if the user is authenticated
                if (!$user) {
                    return response()->json(['error' => 'User not authenticated'], 401);
                }

                // Get the token (from Bearer token in Authorization header)
                $token = $request->bearerToken();

                // Validate token
                if (!$token) {
                    return response()->json(['error' => 'Token missing'], 400);
                }
        
                // Log the login activity
                $activity = UserActivity::create([
                    'user_id' => $user->id,  // Use the authenticated user's ID
                    'token' => $token,  // Storing token (optional, depends on security needs)
                    'action' => 'login',  // Logging action as 'login'
                    'started_at' => Carbon::parse($validated['started_at']),  // Convert to Carbon for consistent format
                ]);
        
                // Return a success response with the activity ID
                return response()->json([
                    'status' => 'success',
                    'activity_id' => $activity->id,
                    'message' => 'Login activity logged successfully',
                ]);
        
            } catch (\Exception $e) {
                // Catch any errors during logging and return a generic error response
                return response()->json([
                    'error' => 'An error occurred while logging the login activity',
                    'message' => $e->getMessage(),
                ], 500);
            }
        }
        
}
