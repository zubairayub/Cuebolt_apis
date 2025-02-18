<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WelcomeScreen;

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
