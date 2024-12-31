<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FirebaseAuthMiddleware
{
    protected $auth;

    public function __construct(FirebaseAuth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Authorization token not found'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $verifiedIdToken = $this->auth->verifyIdToken($token);
            $firebaseUserId = $verifiedIdToken->claims()->get('sub'); // Firebase User ID
            $request->attributes->add(['firebase_user_id' => $firebaseUserId]);
        } catch (\Kreait\Firebase\Exception\Auth\FailedToVerifyToken $e) {
            return response()->json(['error' => 'Invalid or expired Firebase token'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
