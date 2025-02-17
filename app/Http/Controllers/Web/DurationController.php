<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Duration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class DurationController extends Controller
{
    /**
     * Display a listing of durations.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Fetch all durations with pagination for better performance
            $durations = Duration::paginate(10); // You can adjust the number as needed
            return response()->json($durations, 200); // HTTP Status 200
        } catch (Exception $e) {
            // Return a structured error message in case of any failure
            return response()->json(['error' => 'Failed to fetch durations', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created duration in the database.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validation: Use Laravel's built-in validation
        $validated = $request->validate([
            'duration_name' => 'required|string|max:255',
            'duration_in_days' => 'required|integer|min:1',
        ]);

        try {
            // Create the duration
            $duration = Duration::create($validated);
            return response()->json($duration, 201); // HTTP Status 201: Created
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create duration', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified duration.
     *
     * @param Duration $duration
     * @return JsonResponse
     */
    public function show(Duration $duration): JsonResponse
    {
        try {
            // Using route model binding, automatically fetch the duration by id
            return response()->json($duration, 200); // HTTP Status 200
        } catch (ModelNotFoundException $e) {
            // Handle model not found
            return response()->json(['error' => 'Duration not found', 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch duration', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified duration in the database.
     *
     * @param Request $request
     * @param Duration $duration
     * @return JsonResponse
     */
    public function update(Request $request, Duration $duration): JsonResponse
    {
        // Validation: Check if the request data is valid
        $validated = $request->validate([
            'duration_name' => 'required|string|max:255',
            'duration_in_days' => 'required|integer|min:1',
        ]);

        try {
            // Update the duration record
            $duration->update($validated);
            return response()->json($duration, 200); // HTTP Status 200: Updated successfully
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update duration', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified duration from the database.
     *
     * @param Duration $duration
     * @return JsonResponse
     */
    public function destroy(Duration $duration): JsonResponse
    {
        try {
            // Delete the duration
            $duration->delete();
            return response()->json(['message' => 'Duration deleted successfully'], 200); // HTTP Status 200: Deleted
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete duration', 'message' => $e->getMessage()], 500);
        }
    }
}
