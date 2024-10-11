<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DistrictType;
use App\Models\District;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Support\PostGIS;

class UHFSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(DistrictType $district_type, District $district): void
    {


        $uhf_map = json_decode(File::get('database/seeders/maps/uhf.json'));


        $district_type->type = 'uhf';

        $district_type->save();

        $uhf_type = $district_type->select('id')->where('type', 'uhf')->first();

        if(!$uhf_type):
            
            Log::error('UHF does not exist');
            dump('UHF does not exist');
            return;

        endif;

        foreach($uhf_map->features as $feature):
            
            $district->create([
                'district_type_id' => $uhf_type->id,
                'number' => $feature->properties->UHFCODE,
                'geo_type' => District::validateGeoType($feature->geometry->type),
                'polygon' => $feature->geometry->type === 'Polygon' ? PostGIS::createPolygon($feature->geometry->coordinates) : null,
                'multipolygon' => $feature->geometry->type === 'MultiPolygon' ? PostGIS::createMultiPolygon($feature->geometry->coordinates) : null,
            ]);
        
            
        endforeach;

    }
}
