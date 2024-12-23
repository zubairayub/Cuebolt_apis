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
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\MarketPairController;
use App\Http\Controllers\Api\TradingMarketController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\TradeTypeController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\BadgeController;
use App\Http\Controllers\Api\TraderDashboardController;
use App\Http\Controllers\Api\SignalPerformanceController;








// Open Routes
Route::post("register", [ApiController::class, "register"]);
Route::post("verifyotp", [ApiController::class, "verifyOtp"]);
Route::post("login", [ApiController::class, "login"]);
Route::get('/search/traders', [SearchController::class, 'searchTraders']);
Route::get('/search/packages', [SearchController::class, 'searchPackages']);
Route::apiResource('badges', BadgeController::class);





// Protected Routes

Route::get('all/trades', [TradesController::class, 'getAllTrades']);
Route::get('all/traders', [UserProfileController::class, 'getAllTraders']);
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
    // Get Trader Dashboard Data
    Route::get('/trader/dashboard', [TraderDashboardController::class, 'showDashboard']);
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

Route::post('all/packages', [PackagesController::class, 'getAllPackages']);
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

    Route::post('all/my_packages_traders', [PackagesController::class, 'getMyPackagesTraders']);
    // Route to get all packages from all users
   // Route::post('all/packages', [PackagesController::class, 'getAllPackages']);

   // Fetch performance for a specific signal
    Route::get('/signal/{signalId}/performance', [SignalPerformanceController::class, 'show']);

    // Update performance for a specific signal
    Route::post('/signal/{signalId}/performance', [SignalPerformanceController::class, 'update']);

    Route::post('/signal/performance', [SignalPerformanceController::class, 'store']);
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


Route::group(['middleware' => 'auth:api'], function () {
    // Get all orders for the authenticated user
    Route::get('orders', [OrdersController::class, 'index']);

    // Create a new order
    Route::post('orders', [OrdersController::class, 'store']);

    // Get a specific order by ID
    Route::get('orders/{order}', [OrdersController::class, 'show']);

    // Update an order by ID
    Route::put('orders/{order}', [OrdersController::class, 'update']);

    // Delete an order by ID
    Route::delete('orders/{order}', [OrdersController::class, 'destroy']);
});


Route::middleware('auth:api')->group(function () {
    Route::get('/languages', [LanguageController::class, 'index']);
    Route::get('/countries', [CountryController::class, 'index']);
    Route::get('/countries/{country}/cities', [CityController::class, 'getCitiesByCountry']);
    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/market-pairs', [MarketPairController::class, 'index']);
    Route::get('/market-pairs/{id}', [MarketPairController::class, 'show']);
    Route::get('/market-pairs/market/{market_id}', [MarketPairController::class, 'getByMarketId']);
    Route::get('/trading-markets', [TradingMarketController::class, 'index']);
    Route::get('/trading-markets/{id}', [TradingMarketController::class, 'show']);
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::get('/payment-methods/{id}', [PaymentMethodController::class, 'show']);
    Route::get('/trade-types', [TradeTypeController::class, 'index']);
    Route::get('/trade-types/{id}', [TradeTypeController::class, 'show']);
});


Route::middleware('auth:api')->group(function () {
    // Route to bookmark a trader profile
    Route::post('/trader/{traderProfileId}/bookmark', [BookmarkController::class, 'bookmarkTrader']);

    // Route to remove a bookmark from a trader profile
    Route::delete('/trader/{traderProfileId}/unbookmark', [BookmarkController::class, 'unbookmarkTrader']);
});
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::get('/signal/{signalId}/rrr-live', [SignalPerformanceController::class, 'getLiveRRR']);

Route::post('/forgot-password', [ApiController::class, 'forgotPassword']);
Route::post('/reset-password', [ApiController::class, 'resetPassword']);


