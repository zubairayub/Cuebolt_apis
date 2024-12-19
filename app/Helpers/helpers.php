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

if (!function_exists('log_user_activity')) {
    /**
     * Log user activity (login, screen visit, button click, etc.)
     *
     * @param  int  $userId
     * @param  string  $action
     * @param  string|null  $token
     * @param  string|null  $screen
     * @param  string|null  $button
     * @return void
     */
    function log_user_activity($userId, $action, $token = null, $screen = null, $button = null)
    {
        \App\Models\UserActivity::create([
            'user_id' => $userId,
            'token' => $token,
            'action' => $action,
            'screen' => $screen,
            'button' => $button,
            'started_at' => \Carbon\Carbon::now(),
        ]);
    }



    
}


if (!function_exists('calculateRRR')) {

    /**
     * Calculate the Risk-Reward Ratio (RRR).
     *
     * @param  float  $entry
     * @param  float  $takeProfit
     * @param  float  $stopLoss
     * @return array
     */
    function calculateRRR(float $entry, float $takeProfit, float $stopLoss): array
    {
        if ($stopLoss >= $entry || $takeProfit <= $entry) {
            return [
                'error' => 'Invalid input: Stop loss must be less than entry, and take profit must be greater than entry.',
            ];
        }

        // Calculate risk and reward
        $reward = abs($takeProfit - $entry);
        $risk = abs($entry - $stopLoss);

        if ($risk == 0) {
            return [
                'error' => 'Risk cannot be zero.',
            ];
        }

        // Calculate the RRR
        $rrr = $reward / $risk;

        return [
            'entry' => $entry,
            'take_profit' => $takeProfit,
            'stop_loss' => $stopLoss,
            'reward' => $reward,
            'risk' => $risk,
            'rrr' => round($rrr, 2), // Rounded to 2 decimal places
        ];
    }


}

