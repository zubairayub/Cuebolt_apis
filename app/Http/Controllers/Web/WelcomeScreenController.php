<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WelcomeScreen;
use App\Models\Package;
use App\Models\Trade;
use App\Models\UserProfile;
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


    public function home()
    {
        // Get top packages based on highest win percentage
        $topPackages = Package::where('status', 1)
            ->orderByDesc('win_percentage')
            ->take(20)
            ->get();

        // Get top traders based on highest rating from user profiles
        $topTraders = UserProfile::where('trader', 1)
            ->orderByDesc('rating')
            ->take(10)
            ->with('user')
            ->get();

        // Get top signals (trades) based on highest profit/loss percentage
        $topSignals = Trade::orderByDesc('profit_loss')
            ->take(20)
            ->with(['package', 'marketPair', 'tradeType'])
            ->get();

        // Return data to the home view
        return view('home', compact('topPackages', 'topTraders', 'topSignals'));
    }

    public function packages_list()
    {
        // Get top packages based on highest win percentage
        $topPackages = Package::where('status', 1)
            ->orderByDesc('win_percentage')
            ->get();

      
        // Return data to the home view
        return view('inner-pages.packages-list', compact('topPackages'));
    }

    public function trader_dashboard()
    {
        // Get top packages based on highest win percentage
        $topPackages = Package::where('status', 1)
            ->orderByDesc('win_percentage')
            ->get();

      
        // Return data to the home view
        return view('inner-pages.trader-dashboard', compact('topPackages'));
    }


    

}
