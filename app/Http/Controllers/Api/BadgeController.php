<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Controllers\Request;
use App\Models\Badge;

class BadgeController extends Controller
{
    // Display a listing of the badges
    public function index()
    {
        $badges = Badge::all();
        return response()->json($badges);
    }
}
