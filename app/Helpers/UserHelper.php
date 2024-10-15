<?php

use App\Models\UserProfile;
use App\Models\UserDevice;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;







if (!function_exists('getUserDevice')) {
    /**
     * Get the user's device information (Browser, OS, etc.)
     *
     * @return string
     */
    function getUserDevice($user, $token) 
    {
        // Get the user agent from the request header
        $agent = request()->header('User-Agent');  
        
        // Initialize the Agent class for better user-agent parsing
        $agentParser = new Agent();
        $agentParser->setUserAgent($agent);  // Set the user agent manually
    
        // Get details from the user agent
        $device = $agentParser->device();   // Get the device name
        $platform = $agentParser->platform();  // Get the platform (Operating System)
        $browser = $agentParser->browser();  // Get the browser name
        $ipAddress = request()->ip();  // Get the user's IP address
    
        // Save device information to the `user_devices` table
        UserDevice::create([
            'user_id' => $user->id,  // Link to the authenticated user
            'token_id' => $token,  // Link to the provided token ID
            'device' => $device,  // Device name (e.g., iPhone, Android, etc.)
            'platform' => $platform,  // Platform name (e.g., Windows, iOS, Android, etc.)
            'browser' => $browser,  // Browser name (e.g., Chrome, Safari, etc.)
            'ip_address' => $ipAddress,  // User's IP address
        ]);
    
        return "Device: $device, Platform: $platform, Browser: $browser, IP: $ipAddress";
    }
    
}

if (!function_exists('makeUserTrader')) {
 function makeUserTrader()
    {
        // Get the logged-in user
        $user = Auth::user();

        // Check if the user already has a profile and if they are not already a trader
        $profile = UserProfile::where('user_id', $user->id)->first();

        if ($profile && $profile->trader == 0) {
            // Update the profile to set the trader status to 1
            $profile->update(['trader' => 1]);
        }
    }

}

