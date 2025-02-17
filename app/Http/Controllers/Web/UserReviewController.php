<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use App\Models\UserReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;


class UserReviewController extends Controller
{
    // Display reviews for a specific trader
    public function index($trader_id)
    {
        try {
            // Validate that the trader exists
            $reviews = UserReview::where('trader_id', $trader_id)->get();
            
            if ($reviews->isEmpty()) {
                return response()->json(['message' => 'No reviews found for this trader'], 404);
            }

            return response()->json($reviews, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error while retrieving reviews', 'details' => $e->getMessage()], 500);
        }
    }

    // Show a single review
    public function show($id)
    {
        try {
            $review = UserReview::findOrFail($id);
            return response()->json($review, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Review not found', 'details' => $e->getMessage()], 404);
        }
    }

    // Store a new review
    public function store(Request $request)
    {
        $this->validateRequest($request);

        try {
            // Check if the user has already reviewed the trader
            $existingReview = UserReview::where('user_id', Auth::id())
                                        ->where('trader_id', $request->input('trader_id'))
                                        ->first();
        
            if ($existingReview) {
                // If a review already exists, return an error response
                return response()->json(['error' => 'You have already reviewed this trader.'], 409);
            }
        
            // Create a new review if no existing review is found
            $review = UserReview::create([
                'user_id' => Auth::id(),
                'trader_id' => $request->input('trader_id'),
                'rating' => $request->input('rating'),
                'review' => $request->input('review'),
                'reviewer_location' => $request->input('reviewer_location')
            ]);
        
            return response()->json(['message' => 'Review added successfully', 'review' => $review], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error adding review', 'details' => $e->getMessage()], 500);
        }
    }

    // Update an existing review
    public function update(Request $request, $id)
    {
        $this->validateRequest($request);

        try {
            $review = UserReview::findOrFail($id);

            // Ensure the authenticated user is the owner of the review
            if (Auth::id() !== $review->user_id) {
                return response()->json(['error' => 'Unauthorized to update this review'], 403);
            }

            $review->update($request->only(['rating', 'review', 'reviewer_location']));

            return response()->json(['message' => 'Review updated successfully', 'review' => $review], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error updating review', 'details' => $e->getMessage()], 500);
        }
    }

    // Delete a review
    public function destroy($id)
    {
        try {
            $review = UserReview::findOrFail($id);

            // Ensure the authenticated user is the owner of the review
            if (Auth::id() !== $review->user_id) {
                return response()->json(['error' => 'Unauthorized to delete this review'], 403);
            }

            $review->delete();

            return response()->json(['message' => 'Review deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error deleting review', 'details' => $e->getMessage()], 500);
        }
    }

    // Helper method to validate request data
    private function validateRequest(Request $request)
    {
        $request->validate([
            'trader_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000',
            'reviewer_location' => 'nullable|string|max:255',
        ]);
    }
}