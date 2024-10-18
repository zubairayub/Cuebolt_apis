<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketPair;
use Illuminate\Http\Request;


class MarketPairController extends Controller
{
    // Fetch all market pairs
    public function index()
    {
        $marketPairs = MarketPair::all();
        return response()->json($marketPairs, 200);
    }

    // Show a single market pair by ID
    public function show($id)
    {
        $marketPair = MarketPair::find($id);

        if (!$marketPair) {
            return response()->json(['message' => 'Market pair not found'], 404);
        }

        return response()->json($marketPair, 200);
    }

    public function getByMarketId($market_id)
    {
        $marketPairs = MarketPair::where('market_id', $market_id)->get();

        if ($marketPairs->isEmpty()) {
            return response()->json(['message' => 'No market pairs found for this market_id'], 404);
        }

        return response()->json($marketPairs, 200);
    }
}
