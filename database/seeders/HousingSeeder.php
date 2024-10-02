<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Housing;
use App\Models\District;
use App\Models\DistrictType;

class HousingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Housing $housing, District $district, DistrictType $district_type): void
    {

        /**
         * seed council district housing units
         */
        $council_units = json_decode(File::get('database/seeders/json/council-district-housing-units-2020-census.json'));
        
        $council_district_type = $district_type::select('id')->where('type', 'council')->first();

        foreach($council_units as $unit){
            dump($unit);
            $housing->create([
                'district_id' => $district->where([['number', $unit->councildistrict], ['district_type_id', $council_district_type->id]])->first()->id,
                'units' => $unit->units,
                'source' => '2017-2021 American Community Survey 5-Year Data Profiles',
            ]);
        }

        /**
         * 
         * seed senate district housing units
         */

        $senate_units = json_decode(File::get('database/seeders/json/senate-district-housing-units-2017-2022.json'));
        $senate_district_type = $district_type->select('id')->where('type', 'senate')->first();

        foreach($senate_units as $unit){
            
            $housing->create([
                'district_id' => $district->where([['number', $unit->senatedistrict], ['district_type_id', $senate_district_type->id]])->first()->id,
                'units' => $unit->units,
                'source' => '2017-2021 American Community Survey 5-Year Data Profiles',
            ]);
        }


        /**
         * seed assembly district housing units
         * 
         */

        $assembly_units = json_decode(File::get('database/seeders/json/assembly-district-housing-units-2017-2022.json'));
        $assembly_district_type = $district_type->select('id')->where('type', 'assembly')->first();

        foreach($assembly_units as $unit){
            
            $housing->create([
                'district_id' => $district->where([['number', $unit->assemblydistrict], ['district_type_id', $assembly_district_type->id]])->first()->id,
                'units' => $unit->units,
                'source' => '2017-2021 American Community Survey 5-Year Data Profiles',
            ]);
        }

    }
}
