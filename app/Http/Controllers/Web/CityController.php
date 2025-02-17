<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Country;


class CityController extends Controller
{   

    public function index() {
        $countries = City::all();
        return response()->json($countries);
    }

    public function getCitiesByCountry(Country $country) {
        return response()->json($country->cities);
    }
}
