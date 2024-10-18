<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TradeType;

class TradeTypeController extends Controller
{
     // Fetch all trade types
     public function index()
     {
         $tradeTypes = TradeType::all();
         return response()->json($tradeTypes, 200);
     }
 
     // Fetch a specific trade type by ID
     public function show($id)
     {
         $tradeType = TradeType::find($id);
 
         if (!$tradeType) {
             return response()->json(['message' => 'Trade type not found'], 404);
         }
 
         return response()->json($tradeType, 200);
     }
}
