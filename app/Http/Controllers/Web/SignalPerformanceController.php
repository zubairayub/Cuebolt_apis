<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SignalPerformance;
use App\Models\Trade;
use Illuminate\Support\Facades\Http;

class SignalPerformanceController extends Controller
{
    public function show($signalId)
    {
        $performance = SignalPerformance::with('user')->where('signal_id', $signalId)->first();

        if (!$performance) {
            return response()->json(['message' => 'Signal performance not found'], 404);
        }

        return response()->json($performance, 200);
    }


    // Insert a new Signal Performance record
    public function store(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'signal_id' => 'required|exists:trades,id', // Ensure the signal exists
            'user_id' => 'required|exists:users,id',     // Ensure the user exists
            'current_price' => 'required|numeric',
            'entry_price' => 'required|numeric',
            'take_profit' => 'required|numeric',
            'stop_loss' => 'required|numeric',
            
        ]);
    
        // Find the trade based on the signal_id
        $trade = Trade::find($request->signal_id);
    
        // Get the market_pair_id from the trade
        $marketPairId = $trade->market_pair_id;
    
        // Create a new record in the database
        SignalPerformance::create([
            'signal_id' => $request->signal_id,
            'user_id' => auth()->id(),
            'current_price' => $request->current_price,
            'entry_price' => $request->entry_price,
            'take_profit' => $request->take_profit,
            'stop_loss' => $request->stop_loss,
            'status' => 'active',
            'market_pair_id' => $marketPairId,
        ]);
    
        // Redirect back with a success message
        return response()->json(['success' => true, 'message' => 'Signal performance recorded successfully!']);
    }


    public function update(Request $request, $signalId)
    {
        $signal = Trade::find($signalId);

        if (!$signal) {
            return response()->json(['message' => 'Signal not found'], 404);
        }
        $marketPairId =  $signal->market_pair_id;  // Assuming `market_pair_id` is present in the trades table
        $currentPrice = $request->input('current_price');
        $profitLoss = $currentPrice - $signal->entry_price;
        $status = 'active';

        if ($currentPrice >= $signal->take_profit) {
            $status = 'hit_take_profit';
        } elseif ($currentPrice <= $signal->stop_loss) {
            $status = 'hit_stop_loss';
        }

        $performance = SignalPerformance::updateOrCreate(
            ['signal_id' => $signalId, 'user_id' => auth()->id()],
            [
                'current_price' => $currentPrice,
                'profit_loss' => $profitLoss,
                'entry_price' => $signal->entry_price,
                'take_profit' => $signal->take_profit,
                'stop_loss' => $signal->stop_loss,
                'status' => $status,
                'market_pair_id' => $marketPairId,  

            ]
        );

        return response()->json($performance, 200);
    }


    public function getLiveRRR($signalId)
    {
        // Fetch signal performance from the database
        $performance = SignalPerformance::find($signalId);

        if (!$performance) {
            return response()->json(['message' => 'Signal performance not found'], 404);
        }

        // Define the trading pair (e.g., BTCUSDT)
        //$cryptoPair = $performance->trade->crypto_pair ?? 'BTCUSDT';
        // Validate trade relationship
        $trade = $performance->trade;
        if (!$trade) {
            return response()->json(['message' => 'Trade not found'], 404);
        }

        // Validate marketPair relationship
        $marketPair = $trade->marketPair;
        if (!$marketPair) {
            return response()->json(['message' => 'Market pair not found'], 404);
        }

        // Get the symbol for the crypto pair
        $cryptoPair = $marketPair->base_currency . $marketPair->quote_currency;

        // Fetch the live price from Binance API
        $response = Http::get("https://api.binance.com/api/v3/ticker/price?symbol={$cryptoPair}");

        if ($response->failed()) {
            return response()->json(['message' => 'Failed to fetch live price from Binance'], 500);
        }

        $livePrice = $response->json()['price'];

        // Calculate RRR
        $reward = $performance->take_profit - $performance->entry_price;
        $risk = $performance->entry_price - $performance->stop_loss;
        $rrr = $risk > 0 ? round($reward / $risk, 2) : null;

        return response()->json([
            'signal_id' => $signalId,
            'crypto_pair' => $cryptoPair,
            'live_price' => $livePrice,
            'entry_price' => $performance->entry_price,
            'take_profit' => $performance->take_profit,
            'stop_loss' => $performance->stop_loss,
            'rrr' => $rrr,
        ], 200);
    }

}
