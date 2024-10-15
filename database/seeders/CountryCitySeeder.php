<?php

// database/seeders/CountryCitySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\City;

class CountryCitySeeder extends Seeder
{
    public function run()
    {
        // Example countries
        $countries = [
            ['name' => 'United States', 'code' => 'US'],
            ['name' => 'India', 'code' => 'IN'],
            ['name' => 'United Kingdom', 'code' => 'GB'],
        ];

        foreach ($countries as $countryData) {
            $country = Country::create($countryData);

            // Example cities for each country
            if ($country->name == 'United States') {
                City::create(['name' => 'New York', 'country_id' => $country->id]);
                City::create(['name' => 'Los Angeles', 'country_id' => $country->id]);
            } elseif ($country->name == 'India') {
                City::create(['name' => 'Mumbai', 'country_id' => $country->id]);
                City::create(['name' => 'Delhi', 'country_id' => $country->id]);
            } elseif ($country->name == 'United Kingdom') {
                City::create(['name' => 'London', 'country_id' => $country->id]);
                City::create(['name' => 'Manchester', 'country_id' => $country->id]);
            }
        }
    }
}
