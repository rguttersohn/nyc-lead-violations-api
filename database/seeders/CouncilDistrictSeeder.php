<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\CouncilDistrict;
use App\Support\GeoJSON;


class CouncilDistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(CouncilDistrict $council_district): void
    {
        
        $cc_map = json_decode(File::get('database/seeders/maps/nycc.json'));

       
        /**
         * get geographies from geojson file
         */
        foreach($cc_map->features as $feature):
            
            $council_district->create([
                'councildistrict' => $feature->properties->CounDist,
                'geo_type' => CouncilDistrict::validateGeoType($feature->geometry->type),
                'polygon' => $feature->geometry->type === 'Polygon' ? GeoJSON::createPolygon($feature->geometry->coordinates) : null,
                'multipolygon' => $feature->geometry->type === 'MultiPolygon' ? GeoJSON::createMultiPolygon($feature->geometry->coordinates) : null,
            ]);
            

        endforeach;

    }
}
