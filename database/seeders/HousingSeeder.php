<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Housing;
use App\Models\District;

class HousingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Housing $housing, District $district): void
    {

        /**
         * seed council district housing units
         */
        $council_units = json_decode(File::get('database/seeders/json/council-district-housing-units-2020-census.json'));
        
        foreach($council_units as $unit){

            $housing->create([
                'district_id' => $district->select('id')->where('number', $unit->councildistrict)->first()->id,
                'units' => $unit->units,
                'source' => '2017-2021 American Community Survey 5-Year Data Profiles',
            ]);
        }

        /**
         * 
         * seed senate district housing units
         */

        $senate_units = json_decode(File::get('database/seeders/json/senate-district-housing-units-2017-2022.json'));

        foreach($senate_units as $unit){
            
            $housing->create([
                'district_id' => $district->select('id')->where('number', $unit->senatedistrict)->first()->id,
                'units' => $unit->units,
                'source' => '2017-2021 American Community Survey 5-Year Data Profiles',
            ]);
        }


        $assembly_units = json_decode(File::get('database/seeders/json/assembly-district-housing-units-2017-2022.json'));

        foreach($assembly_units as $unit){
            
            $housing->create([
                'district_id' => $district->select('id')->where('number', $unit->assemblydistrict)->first()->id,
                'units' => $unit->units,
                'source' => '2017-2021 American Community Survey 5-Year Data Profiles',
            ]);
        }

    }
}
