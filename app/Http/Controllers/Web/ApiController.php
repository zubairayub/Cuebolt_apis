<?php

namespace App\Http\Controllers\Web;

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
use Illuminate\Support\Facades\Gate;








class ApiController extends Controller
{

    public function register(Request $request)
    {
        try {
           
    
            // Validate request inputs
            $validator =  Validator($request->all(), [
               
                'email' => 'nullable|string|email|max:255|unique:users',
                'phone' => [
                    'nullable',
                    'string',
                    'unique:users',
                    'regex:/^\+?[0-9]{10,15}$/', // 10-15 digits, optional '+'
                ],
                'password' => 'nullable|string|min:8',
                
               
                'trading_capital' => 'nullable|numeric|min:0', // Ensure this is valid
            ]);
    
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Get validated data as an array
    $validated = $validator->validated();
            // Ensure either email or phone is provided
            if (empty($validated['email']) && empty($validated['phone'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Either email or phone number is required.',
                ], 422);
            }
    
            // Prepend "+" to phone number if missing
            if (!empty($validated['phone']) && substr($validated['phone'], 0, 1) !== '+') {
                $validated['phone'] = '+' . $validated['phone'];
            }
    
            // Assign role or default to 'user'
            $role = $request->input('role', 'user');
            $roleId = Role::where('name', $role)->value('id') ?? Role::where('name', 'user')->value('id');
    
            // Generate OTP
            $otp = rand(100000, 999999);
    
            // Handle password based on registration type
           
                if (empty($validated['password'])) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Password is required.',
                    ], 422);
                }
                $password = bcrypt($validated['password']);
            
    
            // Create user
            $user = User::create([
                'username' => $validated['username'] ?? $this->generateUniqueUsername(),
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'password' => $password,
                'role_id' => $roleId,
                'otp' => $otp,
                'trading_capital' => $validated['trading_capital'] ?? 0, // Set the trading_capital
            ]);
    
            // Send OTP (if email is provided)
            if ($user->email) {
                // Uncomment to enable mail functionality
                // Mail::to($user->email)->send(new OtpMail($otp));
            }
    
            // Create user profile
            $profileController = new UserProfileController();
            $profileController->createProfile(new Request(), $user->id);
    
            // Optionally send FCM notification
            if ($user->fcm_token) {
                $title = "Welcome to Cuebolt";
                $body = "Your only Trading Marketplace";
                $type = "Register";
                $data = [];
                // Uncomment to enable push notification functionality
                // send_push_notification($user->fcm_token, $title, $body, $data, $type);
            }
    
             // Authenticate user using Laravel session
             Auth::login($user, true); // 'true' keeps session persistent
    
             // Store user ID in session
             session(['user_id' => $user->id]);

             return redirect()->route('home')->with('success', 'Login successful');

            // Generate Bearer Token
           // $token = $user->createToken('UserAuthToken')->accessToken;
    
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors
            return response()->json([
                'status' => false,
                'message' => 'Database error occurred during registration.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            // Handle generic errors
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    


    function generateUniqueUsername($baseUsername = null, $socialId = null)
    {
        // Default cool, niche-related usernames if baseUsername is null
        $defaultUsernames = [
            'TradeProX',
            'SignalAce',
            'CoinWhiz',
            'BlockSage',
            'CryptoNinja',
            'TradePulse',
            'SignalMaverick',
            'CoinSeeker',
            'CryptoKing',
            'SatoshiMaster'
        ];

        // If base username is not provided, pick a random one from the default list
        if (!$baseUsername) {
            $baseUsername = $defaultUsernames[array_rand($defaultUsernames)];
        }

        // If social ID is null, we'll just generate a username based on the baseUsername
        $username = preg_replace('/\s+/', '_', $baseUsername); // Replace spaces with underscores

        // If socialId is provided, hash it to ensure uniqueness, otherwise just use the base username
        if ($socialId) {
            $hashedUsername = substr(md5($username . $socialId), 0, 8);
        } else {
            $hashedUsername = substr(md5($username), 0, 8); // Only hash the baseUsername
        }

        // Combine the base username with the hash
        $finalUsername = $username . '_' . $hashedUsername;

        // Ensure the final username is unique in the database
        while (User::where('username', $finalUsername)->exists()) {
            $finalUsername = $username . '_' . $hashedUsername . rand(1, 99); // Append a number if the username exists
        }

        return $finalUsername;
    }



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
                $profileController->createProfile(new Request(), $user->id);
                
            }

            // Generate a token for the user if needed (for API-based apps)
            $token = $user->createToken('Social Login')->accessToken;
            // Generate Bearer Token for the user
            

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
    // public function register(Request $request)
    // {
    //     try {
    //         // Custom error message
    //         $messages = [
    //             'username.regex' => 'Spaces are not allowed in the username.', // Custom message for regex validation
    //         ];

    //         // Validate the request inputs
    //         $validated = $request->validate([
    //             'username' => [
    //                 'nullable',            // Allow username to be null
    //                 'string',             // Must be a string
    //                 'max:255',            // Maximum length is 255 characters
    //                 'unique:users',       // Username must be unique in the users table if provided
    //                 'regex:/^\S*$/u'      // No spaces allowed (regex ensures the string contains no whitespace)
    //             ],
    //             'email' => 'nullable|string|email|max:255|unique:users', // Email is nullable, must be unique
    //             'phone' => [
    //                 'nullable', // Phone is optional
    //                 'string', // Ensure phone number is string
    //                 'unique:users', // Ensure phone number is unique
    //                 'regex:/^\+?[0-9]{10,15}$/' // Accepts 10-15 digit numbers, with optional leading '+'
    //             ],
    //             'password' => 'nullable|string|confirmed|min:8', // Password must be confirmed, min 8 chars
    //             'fcm_token' => 'nullable|string|max:255',  // FCM token validation (optional)
    //             'type' => 'nullable|string|max:255',  // FCM token validation (optional)
    //             'facebook_id' => 'nullable|string|max:255|unique:users',
    //             'google_id' => 'nullable|string|max:255|unique:users',
    //         ], $messages);

    //         // Ensure either email or phone is provided
    //         if (empty($validated['email']) && empty($validated['phone'])) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Either email or phone number is required.',
    //             ], 422); // 422 Unprocessable Entity
    //         }

    //         // Prepend "+" to phone number if it's missing
    //         if (!empty($validated['phone']) && substr($validated['phone'], 0, 1) !== '+') {
    //             $validated['phone'] = '+' . $validated['phone'];
    //         }

    //         // Get role from input or default to 'user'
    //         $role = $request->input('role', 'user');
    //         $roleId = Role::where('name', $role)->value('id'); // Get role ID, use value() for faster querying

    //         // Generate a 6-digit OTP
    //         $otp = rand(100000, 999999);

    //         // Check if the user is registering via Google or Facebook
    //         if (in_array($validated['type'], ['google', 'facebook'])) {
    //             // Generate a random password for users registering via social platforms
    //             $password = bcrypt(Str::random(12));  // Generates a 12-character random password
    //         } else {
    //             // Otherwise, ensure the password is required and encrypt it
    //             if (empty($validated['password'])) {
    //                 return response()->json([
    //                     'status' => false,
    //                     'message' => 'Password is required.',
    //                 ], 422); // Return error if password is missing
    //             }

    //             // Encrypt the password if it's provided
    //             $password = bcrypt($validated['password']);
    //         }

    //         // Create new user
    //         $user = User::create([
    //             'username' => $validated['username'] ?? generateUniqueUsername(), // Use null if username is not provided,
    //             'email' => $validated['email'] ?? null, // Null if only phone is provided
    //             'phone' => $validated['phone'] ?? null, // Null if only email is provided
    //             'password' => $password,
    //             'role_id' => $roleId, // Foreign key from roles table
    //             'otp' => $otp, // Store OTP in the user table
    //             'fcm_token' => $validated['fcm_token'] ?? null, // Add FCM token if provided, otherwise null
    //             'social_id' => $validated['type'] ?? null,
    //             'facebook_id' => $validated['facebook_id'] ?? null,
    //             'google_id' => $validated['google_id'] ?? null,
    //         ]);

    //         // Send OTP via email
    //         if ($user->email) {
    //             //Mail::to($user->email)->send(new OtpMail($otp)); // Create OtpMail to send the OTP
    //         }



    //         // After user creation, create the user profile using the controller
    //         $profileController = new UserProfileController();
    //         //$userProfile = $profileController->createProfile(new Request(), $user->id);
    //         $profileController->createProfile(new Request(), $user->id);
    //        // register_user_firestore($user->id,$user->username,$user->email,$userProfile->getPictureUrlAttribute());
    //         // $title = "Welcome to Cuebolt";
    //         // $body = "Your only Trading Marketplace";
    //         // $type = "Register";
    //         // $data = [];

    //         // if ($user->fcm_token) {
    //         //     $token = $user->fcm_token;
    //         //   //  send_push_notification($token, $title, $body, $data, $type );
    //         // }

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'User registered successfully. Please verify the OTP sent to your email.',
    //             'data' => ['user_id' => $user->id, 'otp' => $user->otp],
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'An error occurred during registration.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }



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

                // Generate Bearer Token
            $token = $user->createToken('UserAuthToken')->accessToken;


                return response()->json([
                    'status' => true,
                    'message' => 'Email verified successfully.',
                    'token'=> $token
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
    // public function login(Request $request)
    // {
    //     // Validate incoming request
    //     $request->validate([
    //         'login' => 'nullable|string',  // This will accept email, username, or phone
    //         'password' => 'nullable|string',
    //         'facebook_id' => 'nullable|string|max:255',
    //         'google_id' => 'nullable|string|max:255',
    //     ]);


    //     // Build the query dynamically based on non-null request parameters
    //     $userQuery = User::query();

    //     if (!empty($request->login)) {
    //         $userQuery->where(function ($query) use ($request) {
    //             $query->where('email', $request->login)
    //                 ->orWhere('username', $request->login)
    //                 ->orWhere('phone', $request->login);
    //         });
    //     }

    //     if (!empty($request->facebook_id)) {
    //         $userQuery->orWhere('facebook_id', $request->facebook_id);
    //     }

    //     if (!empty($request->google_id)) {
    //         $userQuery->orWhere('google_id', $request->google_id);
    //     }


    //     // Check if the user exists and password matches
    //     if ($user && (Hash::check($request->password, $user->password) || $request->filled('facebook_id') || $request->filled('google_id'))) {
    //         // Create an authentication token for the user
    //         $token = $user->createToken('MyAppToken')->accessToken;


    //         // Register user device
    //         getUserDevice($user, $user->tokens()->latest()->first()->id); // Pass user and token

    //         // Check if the FCM token is provided in the request
    //         if ($request->has('fcm_token') && !empty($request->fcm_token)) {

    //             // Update FCM token in the users table if it's not null
    //             $user->update(['fcm_token' => $request->fcm_token]);
    //         }

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Login Successful',
    //             'data' => [
    //                 'user' => $user,
    //                 'token' => $token,
    //             ]
    //         ], 200); // HTTP status 200 OK


    //     }

    //     // If user not found or password does not match
    //     return response()->json([
    //         'status' => false,
    //         'message' => 'Invalid login credentials',
    //         'data' => []
    //     ], 401); // HTTP status 401 Unauthorized
    // }



    public function login(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'login' => 'nullable|string|max:255',  // Accepts email, username, or phone
            'password' => 'nullable|string|min:8|max:255',
            'facebook_id' => 'nullable|string|max:255',
            'google_id' => 'nullable|string|max:255',
            'fcm_token' => 'nullable|string|max:255',
        ]);
    
        try {
            // Query user dynamically
            $userQuery = User::query();
    
            if (!empty($validated['login'])) {
                $userQuery->where(function ($query) use ($validated) {
                    $query->where('email', $validated['login'])
                        ->orWhere('username', $validated['login'])
                        ->orWhere('phone', $validated['login']);
                });
            }
    
            if (!empty($validated['facebook_id'])) {
                $userQuery->orWhere('facebook_id', $validated['facebook_id']);
            }
    
            if (!empty($validated['google_id'])) {
                $userQuery->orWhere('google_id', $validated['google_id']);
            }
    
            // Retrieve user
            $user = $userQuery->first();
    
            if (
                $user && (
                    (!empty($validated['password']) && Hash::check($validated['password'], $user->password)) ||
                    $request->filled('facebook_id') ||
                    $request->filled('google_id')
                )
            ) {
                // Authenticate user using Laravel session
                Auth::login($user, true); // 'true' keeps session persistent
    
                // Store user ID in session
                session(['user_id' => $user->id]);
    
                // Store FCM token if provided
                if ($request->filled('fcm_token')) {
                    $user->update(['fcm_token' => $validated['fcm_token']]);
                }
    
                // Redirect to home page
                return redirect()->route('home')->with('success', 'Login successful');
            }
    
            return redirect()->back()->with('error', 'Invalid login credentials');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
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
    try {
        // Logout the authenticated user
        Auth::logout();

        // Clear session data
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to login page or home page
        return redirect()->route('login')->with('success', 'Logout successful');
    } catch (\Throwable $e) {
        return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
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


    public function deleteUser($userId)
    {
        // Check if the user exists
        $user = User::find($userId);
        Log::info('Logged-in user role:', ['role' => auth()->user()->role]);


        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        try {
            // Optionally, use Laravel's Gate to check if the user has permission to delete
            if (Gate::denies('delete-user', $user)) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }

            $user->packages->each(function ($package) {
                $package->trades()->delete();  // Delete trades linked to each package
            });
            
            // Delete user-related data first (if needed)
            $user->profile()->delete();
            $user->packages()->delete();
           
            $user->reviews()->delete();
            $user->faqs()->delete();
            $user->orders()->delete();
            $user->notifications()->delete();

            // Now delete the user
            $user->delete();

            return response()->json(['message' => 'User deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error("Error deleting user: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred while deleting the user.'.$e->getMessage()], 500);
        }
    }
}
