<?php

use App\Http\Controllers\Web\FormsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ApiController;
use App\Http\Controllers\Web\UserActivityController;
use App\Http\Controllers\Web\UserProfileController;
use App\Http\Controllers\Web\FaqController;
use App\Http\Controllers\Web\UserReviewController;
use App\Http\Controllers\Web\DurationController;
use App\Http\Controllers\Web\PackagesController;
use App\Http\Controllers\Web\TradesController;
use App\Http\Controllers\Web\OrdersController;
use App\Http\Controllers\Web\CountryController;
use App\Http\Controllers\Web\CityController;
use App\Http\Controllers\Web\LanguageController;
use App\Http\Controllers\Web\MarketPairController;
use App\Http\Controllers\Web\TradingMarketController;
use App\Http\Controllers\Web\PaymentMethodController;
use App\Http\Controllers\Web\TradeTypeController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\BookmarkController;
use App\Http\Controllers\Web\BadgeController;
use App\Http\Controllers\Web\TraderDashboardController;
use App\Http\Controllers\Web\UserDashboardController;
use App\Http\Controllers\Web\SignalPerformanceController;
use App\Http\Controllers\Web\SubscriptionController;
use App\Http\Controllers\Web\GroupController;
use Kreait\Firebase\Firestore;
use App\Http\Controllers\Web\WelcomeScreenController;
use App\Http\Controllers\Web\FirebaseController;


// Route::get('/', function () {
//     return view('home');
// });

Route::get('/', [WelcomeScreenController::class, 'home'])->name('home');



Route::get('/packages-list', [WelcomeScreenController::class, 'packages_list'])->name('packages.list');


Route::post('/logout', [ApiController::class, 'logout'])->name('logout');

Route::get('/fetch-top-packages', [PackagesController::class, 'fetchTopPackages']);



// Welcome Screen Routes
Route::get('/welcome-screen', [WelcomeScreenController::class, 'getWelcomeScreen']);
Route::post('/welcome-screen', [WelcomeScreenController::class, 'storeWelcomeScreen']);
Route::put('/welcome-screen', [WelcomeScreenController::class, 'updateWelcomeScreen']);

// Group Routes
Route::post('/create-group', [GroupController::class, 'createGroup']);
Route::post('/add-to-group', [GroupController::class, 'addMemberToGroup']);

// Authentication Routes
Route::post("register", [ApiController::class, "register"])->name('register.submit');;
Route::post("verifyotp", [ApiController::class, "verifyOtp"]);
Route::post("login", [ApiController::class, "login"]);
Route::get('/login', [FormsController::class, 'login'])->name('login.user');
Route::get('/register', [FormsController::class, 'register'])->name('register.user');
Route::get("logout", [ApiController::class, "logout"])->middleware('auth');

// Search Routes
Route::get('/search/traders', [SearchController::class, 'searchTraders']);
Route::get('/search/packages', [SearchController::class, 'searchPackages']);

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get("profile", [UserProfileController::class, "profile"]);
    Route::post("create-profile", [UserProfileController::class, "createProfile"]);
    Route::post("show-profile", [UserProfileController::class, "showProfile"]);
});

// User Activity Routes
Route::middleware('auth')->group(function () {
    Route::post("/log-login", [UserActivityController::class, "logLogin"]);
    Route::post("/log-screen-visit", [UserActivityController::class, "logScreenVisit"]);
    Route::post("/log-screen-exit", [UserActivityController::class, "logScreenExit"]);
    Route::post("/log-button-click", [UserActivityController::class, "logButtonClick"]);
    Route::post("/log-activities", [UserActivityController::class, "logBatchActivities"]);
});

// FAQ Routes
Route::middleware('auth')->group(function () {
    Route::resource('/faqs', FaqController::class);
});

// Dashboard Routes
Route::middleware('auth.redirect')->group(function () {
    // Route::get('/trader/dashboard', [DashboardController::class, 'showTraderDashboard']);
    
    // Route::get('/user/dashboard', [DashboardController::class, 'showUserDashboard']);
});

Route::get('/trader-dashboard/{username}', [TraderDashboardController::class, 'showDashboard'])->name('trader.dashboard');


// User Reviews Routes
Route::middleware('auth')->group(function () {
    //  Route::resource('/reviews', UserReviewController::class);
});

// Duration Routes
Route::resource('durations', DurationController::class);

// Package Routes

Route::middleware('auth.redirect')->group(function () {
    Route::resource('packages', PackagesController::class);
    Route::post('all/my_packages_traders', [PackagesController::class, 'getMyPackagesTraders']);
    Route::get('/package/add', [FormsController::class, 'viewPackageForm'])->name('package.addform');

    Route::get('/signal/add', [FormsController::class, 'viewSignalForm'])->name('signal.addform');

});

// Trade Routes
Route::middleware('auth')->group(function () {
    Route::resource('trades', TradesController::class);
    Route::post('/trade-journal', [TradesController::class, 'storeTradeJournal']);
    Route::post('/update-trade-journal', [TradesController::class, 'updateTradeJournal']);
});

// Order Routes
Route::middleware('auth')->group(function () {
    Route::resource('orders', OrdersController::class);
});

// Signal Performance Routes
Route::middleware('auth')->group(function () {
    Route::get('/signal/{signalId}/performance', [SignalPerformanceController::class, 'show']);
    Route::post('/signal/{signalId}/performance', [SignalPerformanceController::class, 'update']);
    Route::post('/signal/performance', [SignalPerformanceController::class, 'store']);
});
