<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

// Open Routes
Route::post("register", [ApiController::class, "register"]);
Route::post("verifyotp", [ApiController::class, "verifyOtp"]);
Route::post("login", [ApiController::class, "login"]);
Route::post('login/facebook/token', [ApiController::class, 'loginWithFacebookToken']);




// Protected Routes
Route::group([
    "middleware" => ["auth:api"]
],  function(){

    Route::get("profile", [ApiController::class, "profile"]);
    Route::get("logout", [ApiController::class, "logout"]);
});


Route::middleware('api')->group(function () {
    // Social Login
    Route::get("/authenticate/redirect/{social}",[ApiController::class,"socialiteRedirect"])->name("socialite-redirect");
    Route::get("/authenticate/callback/{social}",[ApiController::class,"callbacksocialite"])->name("socialite-callback");
 });


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');




