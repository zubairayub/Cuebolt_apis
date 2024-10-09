<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail; // Import Mail Facade
use App\Mail\OtpMail; // Import OtpMail class
use Carbon\Carbon; // Include Carbon for date formatting
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;


class ApiController extends Controller
{


    /**
 * Register a new user.
 * 
 * POST [name, email, phone, password, password_confirmation, role (optional)]
 * 
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function register(Request $request) 
{
    try {
        // Validate the request inputs
        $validated = $request->validate([
            'name' => 'required|string|max:255', // User's name is required
            'email' => 'nullable|string|email|max:255|unique:users', // Email is nullable, must be unique
            'phone' => [
                'nullable', // Phone is optional
                'string', // Ensure phone number is string
                'unique:users', // Ensure phone number is unique
                'regex:/^\+?[0-9]{10,15}$/' // Accepts 10-15 digit numbers, with optional leading '+'
            ],
            'password' => 'required|string|confirmed|min:8', // Password must be confirmed, min 8 chars
        ]);

        // Ensure either email or phone is provided
        if (empty($validated['email']) && empty($validated['phone'])) {
            return response()->json([
                'status' => false,
                'message' => 'Either email or phone number is required.',
            ], 422); // 422 Unprocessable Entity
        }

        // Prepend "+" to phone number if it's missing
        if (!empty($validated['phone']) && substr($validated['phone'], 0, 1) !== '+') {
            $validated['phone'] = '+' . $validated['phone'];
        }

        // Get role from input or default to 'user'
        $role = $request->input('role', 'user');
        $roleId = Role::where('name', $role)->value('id'); // Get role ID, use value() for faster querying

        // Generate a 6-digit OTP
        $otp = rand(100000, 999999);

        // Create new user
        $user = User::create([
            'username' => $validated['name'],
            'email' => $validated['email'] ?? null, // Null if only phone is provided
            'phone' => $validated['phone'] ?? null, // Null if only email is provided
            'password' => bcrypt($validated['password']),
            'role_id' => $roleId, // Foreign key from roles table
            'otp' => $otp, // Store OTP in the user table
        ]);

        // Send OTP via email
        if ($user->email) {
            Mail::to($user->email)->send(new OtpMail($otp)); // Create OtpMail to send the OTP
        }

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully. Please verify the OTP sent to your email.',
            'data' => ['user_id' => $user->id],
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred during registration.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


/**
 * Verify the OTP sent to the user's email.
 * 
 * POST [user_id, otp]
 * 
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function verifyOtp(Request $request)
{
    // Validate the request input
    $request->validate([
        'otp' => 'required|string|size:6', // Ensure OTP is the correct length
        'user_id' => 'required|exists:users,id', // Ensure the user exists
    ]);

    // Find the user by ID
    $user = User::find($request->user_id);

    // Check if the user exists and OTP matches
    if ($user && $user->otp === $request->otp) {
        try {
            // Update the user, setting OTP to null and marking email as verified
            $user->update([
                'otp' => null, // Clear the OTP
                'email_verified_at' => now(), // Set email_verified_at to current timestamp
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Email verified successfully.',
            ]);
        } catch (\Exception $e) {
            // Log any exceptions for debugging
            \Log::error('Error during OTP verification: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Could not verify email. Please try again later.',
            ], 500);
        }
    } else {
        return response()->json([
            'status' => false,
            'message' => 'Invalid OTP or user not found.',
        ], 422); // Unprocessable Entity
    }
}



    /**
     * Handle user login with email, username, or phone number.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'login' => 'required|string',  // This will accept email, username, or phone
            'password' => 'required|string',
        ]);

        // Attempt to find the user by email, username, or phone
        $user = User::where('email', $request->login)
                    ->orWhere('username', $request->login)
                    ->orWhere('phone', $request->login)
                    ->first();

        // Check if the user exists and password matches
        if ($user && Hash::check($request->password, $user->password)) {
            // Create an authentication token for the user
            $token = $user->createToken('MyAppToken')->accessToken;

            return response()->json([
                'status' => true,
                'message' => 'Login Successful',
                'token' => $token,
                'data' => [
                    'user' => $user // Optional: include user data if needed
                ]
            ], 200); // HTTP status 200 OK
        }

        // If user not found or password does not match
        return response()->json([
            'status' => false,
            'message' => 'Invalid login credentials',
            'data' => []
        ], 401); // HTTP status 401 Unauthorized
    }


    /**
     * Retrieve the authenticated user's profile information.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        // Retrieve the authenticated user using the auth helper
        $userData = auth()->user();

        // Check if the user is authenticated
        if (!$userData) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Please log in.',
                'data' => null
            ], 401); // HTTP status 401 Unauthorized
        }

        // Prepare the response with user data
        return response()->json([
            'status' => true,
            'message' => 'Profile information retrieved successfully.',
            'data' => [
                'id' => $userData->id,
                'username' => $userData->username,
                'email' => $userData->email,
                'phone' => $userData->phone,
                'created_at' => format_date($userData->created_at), // Using the helper function
    
                // Include any additional fields as necessary
            ]
        ], 200); // HTTP status 200 OK
    }

   /**
     * Log the user out and revoke the access token.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Please log in first.',
            ], 401); // HTTP status 401 Unauthorized
        }

        try {
            // Get the currently authenticated user
            $user = Auth::user();
            
            // Get the user's token and revoke it
            $token = $user->token();
            $token->revoke();

            return response()->json([
                'status' => true,
                'message' => 'User logged out successfully.',
            ], 200); // HTTP status 200 OK
        } catch (ModelNotFoundException $e) {
            // Handle the case where the token is not found
            return response()->json([
                'status' => false,
                'message' => 'Token not found or already revoked.',
            ], 404); // HTTP status 404 Not Found
        } catch (\Exception $e) {
            // Handle any other exceptions that may occur
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while logging out. Please try again later.',
                'error' => $e->getMessage(), // Optionally include the error message for debugging
            ], 500); // HTTP status 500 Internal Server Error
        }
    }
}
