<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\SenateHousing;

class SenateHousingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(SenateHousing $senate_housing): void
    {
        $senate_units = json_decode(File::get('database/seeders/json/senate-district-housing-units-2017-2022.json'));

        foreach($senate_units as $unit){
            
            $senate_housing->create([
                'senatedistrict' => $unit->senatedistrict,
                'units' => $unit->units,
                'source' => '2017-2021 American Community Survey 5-Year Data Profiles',
                'relevance_start' => '2017-01-01',
                'relevance_end' => '2022-01-01'
            ]);
        }
    }
}
