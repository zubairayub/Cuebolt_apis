<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TradingMarket;

class TradingMarketController extends Controller
{
    // Fetch all trading markets
    public function index()
    {
        $tradingMarkets = TradingMarket::all();
        return response()->json($tradingMarkets, 200);
    }

    // Fetch a specific trading market by ID
    public function show($id)
    {
        $tradingMarket = TradingMarket::find($id);

        if (!$tradingMarket) {
            return response()->json(['message' => 'Trading market not found'], 404);
        }

        return response()->json($tradingMarket, 200);
    }
}
