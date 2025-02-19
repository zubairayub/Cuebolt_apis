<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\WelcomeScreen;
use App\Models\Package;
use App\Models\Trade;
use App\Models\TradeType;
use App\Models\MarketPair;
use App\Models\UserProfile;
use App\Models\{TradingMarket, Duration};
use Illuminate\Support\Facades\Storage;


class FormsController extends Controller
{

    // View package form with market type and subscription type
    public function viewPackageForm()
    {
         $marketTypes = TradingMarket::all();
        $subscriptionTypes = Duration::all();
        return view('forms.add_package', compact('marketTypes', 'subscriptionTypes'));
    }

    public function viewSignalForm()
    {
               // Get authenticated user's packages (id & name)
        $userPackages = Package::where('user_id', Auth::id())->select('id', 'name')->get();
        $marketpair = MarketPair::all();
        $tradetype = TradeType::all();
        $marketTypes = TradingMarket::all();
        $subscriptionTypes = Duration::all();
        return view('forms.add_signal', compact('marketTypes', 'subscriptionTypes', 'tradetype', 'marketpair', 'userPackages'));
    }


    public function login()
    {

        return view('forms.login');
    }
    public function register()
    {

        return view('forms.register');
    }
}