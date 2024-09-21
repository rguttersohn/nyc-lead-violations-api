<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AssemblyHousing;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

class AssemblyHousingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(AssemblyHousing $assembly_housing): void
    {
        
        $assembly_units = json_decode(File::get('database/seeders/json/assembly-district-housing-units-2017-2022.json'));

        foreach($assembly_units as $unit){
            
            $assembly_housing->create([
                'assemblydistrict' => $unit->assemblydistrict,
                'units' => $unit->units,
                'source' => '2017-2021 American Community Survey 5-Year Data Profiles',
                'relevance_start' => '2017-01-01',
                'relevance_end' => '2022-01-01'
            ]);
        }

    }
}
