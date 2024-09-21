<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\CouncilHousing;

class CouncilHousingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(CouncilHousing $council_housing): void
    {
        $council_units = json_decode(File::get('database/seeders/json/council-district-housing-units-2020-census.json'));

        foreach($council_units as $unit){
            
            $council_housing->create([
                'councildistrict' => $unit->councildistrict,
                'units' => $unit->units,
                'source' => '2017-2021 American Community Survey 5-Year Data Profiles',
                'relevance_start' => '2017-01-01',
                'relevance_end' => '2022-01-01'
            ]);
        }
    }
}
