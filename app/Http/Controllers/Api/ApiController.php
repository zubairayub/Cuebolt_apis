<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\UserActivity;
use App\Models\Package;
use App\Models\FAQ;
use App\Models\Trade;
use App\Models\DynamicText;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail; // Import Mail Facade
use App\Mail\OtpMail; // Import OtpMail class
use Carbon\Carbon; // Include Carbon for date formatting
use Illuminate\Support\Str; // Import the Str class
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Api\UserProfileController;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;








class ApiController extends Controller
{





    public function socialiteRedirect($social)
    {

        return Socialite::driver($social)->stateless()->redirect();
    }


    public function callbacksocialite(Request $request, $social)
    {

        try {
            // Retrieve the user's information from the social platform (Facebook or Google)
            $socialUser = Socialite::driver($social)->stateless()->user();

            // Extract data from the social profile
            $socialId = $socialUser->getId();
            $name = $socialUser->getName();
            $email = $socialUser->getEmail();
            $phone = $socialUser->user['phone'] ?? null; // Assuming phone number might be available

            // Check if the user exists based on social_id (Facebook or Google), email, or phone
            $user = User::where(function ($query) use ($socialId, $email, $phone) {
                $query->where('facebook_id', $socialId) // For Facebook login
                    ->orWhere('google_id', $socialId) // For Google login
                    ->orWhere('email', $email);
                // Only include phone in the query if it's not empty or null
                if (!empty($phone)) {
                    $query->orWhere('phone', $phone);
                }
            })->first();

            // Generate a unique username
            $username = generateUniqueUsername($name, $socialId);

            if ($user) {
                // Prepare data for update
                $updatedData = [
                    'username' => $username !== $user->username ? $username : null, // Update only if changed
                    'email' => $email !== $user->email ? $email : null, // Update email if changed
                    'phone' => $phone !== $user->phone ? $phone : null, // Update phone if changed
                    $social === 'facebook' ? 'facebook_id' : 'google_id' => $socialId, // Set appropriate social ID
                ];

                // Filter out null values
                $updatedData = array_filter($updatedData);

                // Update user if there's any data to change
                if (!empty($updatedData)) {
                    $user->update($updatedData);
                }
            } else {
                // If the user doesn't exist, create a new one
                $user = User::create([
                    'username' => $username, // Use the generated unique username
                    'email' => $email ?? null, // Use email if available
                    'phone' => $phone ?? null, // Use phone if available
                    'facebook_id' => $social === 'facebook' ? $socialId : null, // Store Facebook ID
                    'google_id' => $social === 'google' ? $socialId : null, // Store Google ID
                    'password' => bcrypt(Str::random(16)), // Generate a random password
                    'role_id' => Role::where('name', 'user')->value('id'), // Default role 'user'
                ]);


                // After user creation, create the user profile using the controller
                $profileController = new UserProfileController();
                $profileController->createProfile($user->id);
            }

            // Generate a token for the user if needed (for API-based apps)
            $token = $user->createToken('Social Login')->accessToken;

            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully.',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred during Facebook login.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




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
            // Custom error message
            $messages = [
                'username.regex' => 'Spaces are not allowed in the username.', // Custom message for regex validation
            ];

            // Validate the request inputs
            $validated = $request->validate([
                'username' => [
                    'nullable',            // Allow username to be null
                    'string',             // Must be a string
                    'max:255',            // Maximum length is 255 characters
                    'unique:users',       // Username must be unique in the users table if provided
                    'regex:/^\S*$/u'      // No spaces allowed (regex ensures the string contains no whitespace)
                ],
                'email' => 'nullable|string|email|max:255|unique:users', // Email is nullable, must be unique
                'phone' => [
                    'nullable', // Phone is optional
                    'string', // Ensure phone number is string
                    'unique:users', // Ensure phone number is unique
                    'regex:/^\+?[0-9]{10,15}$/' // Accepts 10-15 digit numbers, with optional leading '+'
                ],
                'password' => 'nullable|string|confirmed|min:8', // Password must be confirmed, min 8 chars
                'fcm_token' => 'nullable|string|max:255',  // FCM token validation (optional)
                'type' => 'nullable|string|max:255',  // FCM token validation (optional)
                'facebook_id' => 'nullable|string|max:255|unique:users',
                'google_id' => 'nullable|string|max:255|unique:users',
            ], $messages);

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

            // Check if the user is registering via Google or Facebook
            if (in_array($validated['type'], ['google', 'facebook'])) {
                // Generate a random password for users registering via social platforms
                $password = bcrypt(Str::random(12));  // Generates a 12-character random password
            } else {
                // Otherwise, ensure the password is required and encrypt it
                if (empty($validated['password'])) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Password is required.',
                    ], 422); // Return error if password is missing
                }

                // Encrypt the password if it's provided
                $password = bcrypt($validated['password']);
            }

            // Create new user
            $user = User::create([
                'username' => $validated['username'] ?? generateUniqueUsername(), // Use null if username is not provided,
                'email' => $validated['email'] ?? null, // Null if only phone is provided
                'phone' => $validated['phone'] ?? null, // Null if only email is provided
                'password' => $password,
                'role_id' => $roleId, // Foreign key from roles table
                'otp' => $otp, // Store OTP in the user table
                'fcm_token' => $validated['fcm_token'] ?? null, // Add FCM token if provided, otherwise null
                'social_id' => $validated['type'] ?? null,
                'facebook_id' => $validated['facebook_id'] ?? null,
                'google_id' => $validated['google_id'] ?? null,
            ]);

            // Send OTP via email
            if ($user->email) {
                Mail::to($user->email)->send(new OtpMail($otp)); // Create OtpMail to send the OTP
            }



            // After user creation, create the user profile using the controller
            $profileController = new UserProfileController();
            $userProfile = $profileController->createProfile(new Request(), $user->id);
            register_user_firestore($user->id,$user->username,$user->email,$userProfile->getPictureUrlAttribute());
            $title = "Welcome to Cuebolt";
            $body = "Your only Trading Marketplace";
            $type = "Register";
            $data = [];

            if ($user->fcm_token) {
                $token = $user->fcm_token;
                send_push_notification($token, $title, $body, $data, $type );
            }

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully. Please verify the OTP sent to your email.',
                'data' => ['user_id' => $user->id, 'otp' => $user->otp],
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
                Log::error('Error during OTP verification: ' . $e->getMessage());

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
            'login' => 'nullable|string',  // This will accept email, username, or phone
            'password' => 'nullable|string',
            'facebook_id' => 'nullable|string|max:255',
            'google_id' => 'nullable|string|max:255',
        ]);


        // Build the query dynamically based on non-null request parameters
        $userQuery = User::query();

        if (!empty($request->login)) {
            $userQuery->where(function ($query) use ($request) {
                $query->where('email', $request->login)
                    ->orWhere('username', $request->login)
                    ->orWhere('phone', $request->login);
            });
        }

        if (!empty($request->facebook_id)) {
            $userQuery->orWhere('facebook_id', $request->facebook_id);
        }

        if (!empty($request->google_id)) {
            $userQuery->orWhere('google_id', $request->google_id);
        }

        // Execute the query and get the first matching user
        $user = $userQuery->first();
        $title = "Welcome Back";
        $body = "View your signals";
        $type = "Login";
        $token = $user->fcm_token;
        $data = [];

        if ($token) {
            //send_push_notification([$token], $title, $body, $data, $type );
        }

        // Check if the user exists and password matches
        if ($user && (Hash::check($request->password, $user->password) || $request->filled('facebook_id') || $request->filled('google_id'))) {
            // Create an authentication token for the user
            $token = $user->createToken('MyAppToken')->accessToken;


            // Register user device
            getUserDevice($user, $user->tokens()->latest()->first()->id); // Pass user and token

            // Check if the FCM token is provided in the request
            if ($request->has('fcm_token') && !empty($request->fcm_token)) {

                // Update FCM token in the users table if it's not null
                $user->update(['fcm_token' => $request->fcm_token]);
            }
          
            return response()->json([
                'status' => true,
                'message' => 'Login Successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
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




    public function forgotPassword(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if the user exists
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found with this email address.',
            ], 404);
        }

        // Generate an OTP
        $otp = rand(100000, 999999);

        // Store OTP in the database
        User::updateOrCreate(
            ['email' => $user->email],
            [
                'otp' => $otp,
                'updated_at' => Carbon::now()->addMinutes(10), // OTP expires in 10 minutes
            ]
        );

        // Generate the reset token
        $token = Password::createToken($user);

        // Send the OTP to the user via email
        Mail::raw('Your OTP is: ' . $otp, function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Password Reset OTP');
        });

        return response()->json([
            'status' => true,
            'message' => 'An OTP has been sent to your email address.',
        ], 200);
    }

    // public function resetPassword(Request $request)
    // {
    //     // Validate the incoming request
    //     $request->validate([
    //         'token' => 'required',
    //         'email' => 'required|email',
    //         'password' => 'required|string|min:8|confirmed',
    //     ]);

    //     // Find the user based on the email
    //     $user = User::where('email', $request->email)->first();

    //     if (!$user) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Invalid email address.',
    //         ], 404);
    //     }

    //     // Verify the reset token
    //     $status = Password::tokenExists($user, $request->token);

    //     if ($status) {
    //         // Update the user's password
    //         $user->password = Hash::make($request->password);
    //         $user->save();

    //         // Optionally delete the token
    //         Password::deleteToken($user);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Password has been reset successfully.',
    //         ], 200);
    //     }

    //     return response()->json([
    //         'status' => false,
    //         'message' => 'Invalid or expired token.',
    //     ], 400);
    // }



    public function resetPassword(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|string|min:8',
        ]);

        // Find the user based on the email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email address.',
            ], 404);
        }

        // Verify the OTP
        $otpRecord = User::where('email', $request->email)->first();

        if (!$otpRecord || $otpRecord->otp !== $request->otp) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP.',
            ], 400);
        }



        // Check if OTP has expired
        // if (Carbon::now()->greaterThan($otpRecord->updated_at)) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'OTP has expired.'.$otpRecord->updated_at,
        //     ], 400);
        // }

        // Update the user's password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the OTP record after successful reset
        // Clear the OTP column
        $otpRecord->otp = null;
        $otpRecord->save();

        return response()->json([
            'status' => true,
            'message' => 'Password has been reset successfully.',
        ], 200);
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

            // Log the login activity using the helper
            log_user_activity($user->id, 'logout', $token->id);

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


    public function homescreentext()
    {
        $homeText = DynamicText::where('key', 'home_text')->get();
        return response()->json([
            'status' => true,
            'data' => $homeText,
        ]);
    }
}
