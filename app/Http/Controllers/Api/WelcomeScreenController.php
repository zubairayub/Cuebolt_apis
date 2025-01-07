<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WelcomeScreen;

class WelcomeScreenController extends Controller
{
     // Fetch welcome screen data
     public function getWelcomeScreen()
     {
         $welcomeScreen = WelcomeScreen::where('status', true)->get();
 
         if ($welcomeScreen) {
             return response()->json([
                 'success' => true,
                 'data' => $welcomeScreen,
             ]);
         }
 
         return response()->json([
             'success' => false,
             'message' => 'No welcome screen available.',
         ]);
     }
 
     // Store a new welcome screen
     public function storeWelcomeScreen(Request $request)
     {
         $validated = $request->validate([
             'title' => 'nullable|string|max:255',
             'description' => 'nullable|string',
             'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
             'status' => 'nullable|boolean',
             'key' => 'nullable|string',
         ]);
 
         // Handle file upload if an image is provided
         if ($request->hasFile('image')) {
             $imagePath = $request->file('image')->store('welcome_screen_images', 'public');
             $validated['image'] = $imagePath;
         }
 
         $welcomeScreen = WelcomeScreen::create($validated);
 
         return response()->json([
             'success' => true,
             'message' => 'Welcome screen created successfully.',
             'data' => $welcomeScreen,
         ]);
     }
 
     // Update welcome screen data
     public function updateWelcomeScreen(Request $request)
     {
         $validated = $request->validate([
             'title' => 'nullable|string|max:255',
             'description' => 'nullable|string',
             'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
             'status' => 'nullable|boolean',
         ]);
 
         $welcomeScreen = WelcomeScreen::first();
 
         if (!$welcomeScreen) {
             return response()->json([
                 'success' => false,
                 'message' => 'Welcome screen not found. Use the store API to create one.',
             ]);
         }
 
         // Handle file upload if an image is provided
         if ($request->hasFile('image')) {
             $imagePath = $request->file('image')->store('welcome_screen_images', 'public');
             $validated['image'] = $imagePath;
         }
 
         $welcomeScreen->update($validated);
 
         return response()->json([
             'success' => true,
             'message' => 'Welcome screen updated successfully.',
             'data' => $welcomeScreen,
         ]);
     }
}
