<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\UserActivityController;




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


 Route::group([
    "middleware" => ["auth:api"]
], function () {
    // Log individual user activity
    Route::post("/log-login", [UserActivityController::class, "logLogin"]); // Login activity
    Route::post("/log-screen-visit", [UserActivityController::class, "logScreenVisit"]); // Screen visit activity
    Route::post("/log-screen-exit", [UserActivityController::class, "logScreenExit"]); // Screen exit activity
    Route::post("/log-button-click", [UserActivityController::class, "logButtonClick"]); // Button click activity

    // Batch log activities
    Route::post("/log-activities", [UserActivityController::class, "logBatchActivities"]); // Log batch activities
});




// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');




