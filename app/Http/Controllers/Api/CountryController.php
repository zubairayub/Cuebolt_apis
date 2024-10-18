<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;

class CountryController extends Controller
{
    public function index() {
        $countries = Country::with('cities')->get();
        return response()->json($countries);
    }
}
