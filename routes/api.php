<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\UserActivityController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\UserReviewController;
use App\Http\Controllers\Api\DurationController;
use App\Http\Controllers\Api\PackagesController;
use App\Http\Controllers\Api\TradesController;






// Open Routes
Route::post("register", [ApiController::class, "register"]);
Route::post("verifyotp", [ApiController::class, "verifyOtp"]);
Route::post("login", [ApiController::class, "login"]);





// Protected Routes
Route::group([
    "middleware" => ["auth:api"]
],  function(){

    Route::get("profile", [ApiController::class, "profile"]);
    Route::get("logout", [ApiController::class, "logout"]);
    Route::post("create-profile", [UserProfileController::class, "createProfile"]);
    Route::post("show-profile", [UserProfileController::class, "showProfile"]);
    
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




Route::group([
    "middleware" => ["auth:api"]
], function () {
   // Store a new FAQ
   Route::post('/faqs', [FaqController::class, 'store']);
    
   // Get all FAQs for authenticated user
   Route::get('/faqs', [FaqController::class, 'index']);
   
   // Get a single FAQ
   Route::get('/faqs/{id}', [FaqController::class, 'show']);
   
   // Update a FAQ
   Route::PUT('/faqs/{id}', [FaqController::class, 'update']);
   
   // Delete a FAQ
   Route::delete('/faqs/{id}', [FaqController::class, 'destroy']);
});



Route::group([
    "middleware" => ["auth:api"]
], function () {
    // Get all reviews for a trader
    Route::get('/trader/{trader}/reviews', [UserReviewController::class, 'index'])->name('reviews.index');

    // Get a single review
    Route::get('/reviews/{review}', [UserReviewController::class, 'show'])->name('reviews.show');

    // Create a new review
    Route::post('/reviews', [UserReviewController::class, 'store'])->name('reviews.store');

    // Update an existing review
    Route::put('/reviews/{review}', [UserReviewController::class, 'update'])->name('reviews.update');

    // Delete a review
    Route::delete('/reviews/{review}', [UserReviewController::class, 'destroy'])->name('reviews.destroy');
});


Route::prefix('durations')->group(function () {
    Route::get('/', [DurationController::class, 'index']);          // List all durations
    Route::post('/', [DurationController::class, 'store']);         // Create a new duration
    Route::get('{duration}', [DurationController::class, 'show']);  // Show a specific duration
    Route::put('{duration}', [DurationController::class, 'update']); // Update a specific duration
    Route::delete('{duration}', [DurationController::class, 'destroy']); // Delete a specific duration
});

Route::group(['middleware' => 'auth:api'], function () {
    // Get all packages for the authenticated user
    Route::get('packages', [PackagesController::class, 'index']);

    // Create a new package
    Route::post('packages', [PackagesController::class, 'store']);

    // Get a specific package by ID
    Route::get('packages/{id}', [PackagesController::class, 'show']);

    // Update a package by ID
    Route::put('packages/{id}', [PackagesController::class, 'update']);

    // Delete a package by ID
    Route::delete('packages/{id}', [PackagesController::class, 'destroy']);

    // Route to get all packages from all users
    Route::get('all/packages', [PackagesController::class, 'getAllPackages']);
});


Route::group(['middleware' => 'auth:api'], function () {
    // Get all trades for the authenticated user
    Route::get('trades', [TradesController::class, 'index']);

    // Create a new trade
    Route::post('trades', [TradesController::class, 'store']);

    // Get a specific trade by ID
    Route::get('trades/{trade}', [TradesController::class, 'show']);

    // Update a trade by ID
    Route::put('trades/{trade}', [TradesController::class, 'update']);

    // Delete a trade by ID
    Route::delete('trades/{trade}', [TradesController::class, 'destroy']);
});




// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');




