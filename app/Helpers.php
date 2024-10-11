<?php
use App\Models\User;

if (!function_exists('format_date')) {
    /**
     * Format a date to a readable format.
     *
     * @param string $date
     * @return string
     */
    function format_date($date)
    {
        return \Carbon\Carbon::parse($date)->toFormattedDateString(); // Customize as needed
    }

    function format_date_time($date)
    {
        return \Carbon\Carbon::parse($date)->toDateTimeString(); // Customize as needed
    }

    function custom_date_time($date)
    {
        return \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s'); // Customize as needed
    }
    
    
}

if (!function_exists('generateUniqueUsername')) {
 function  generateUniqueUsername($baseUsername, $socialId)
    {
        // Create a base username
        $username = preg_replace('/\s+/', '_', $baseUsername); // Replace spaces with underscores

        // Hash the username and socialId to ensure uniqueness
        $hashedUsername = substr(md5($username . $socialId), 0, 8); // Use the first 8 characters of the hash

        // Combine the base username with the hash
        $finalUsername = $username . '_' . $hashedUsername;

        // Ensure the final username is unique in the database
        while (User::where('username', $finalUsername)->exists()) {
            $finalUsername = $username . '_' . $hashedUsername . rand(1, 99); // Append a number if the username exists
        }

        return $finalUsername;
    }
}
