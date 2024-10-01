<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\District;
use App\Support\GeoJSON;
use App\Models\DistrictType;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run( District $district, DistrictType $district_type): void
    {

        $ad_map = json_decode(File::get('database/seeders/maps/nyad.json'));

        /**
         * get assembly geographies from geojson file
         */

        $assembly_id = $district_type->select('id')->where('type', 'assembly')->first()->id;
       
        foreach($ad_map->features as $feature):
            
            $district->create([
                'district_type_id' => $assembly_id,
                'number' => $feature->properties->AssemDist,
                'geo_type' => District::validateGeoType($feature->geometry->type),
                'polygon' => $feature->geometry->type === 'Polygon' ? GeoJSON::createPolygon($feature->geometry->coordinates) : null,
                'multipolygon' => $feature->geometry->type === 'MultiPolygon' ? GeoJSON::createMultiPolygon($feature->geometry->coordinates) : null,
            ]);
        
            
        endforeach;


        $ss_map = json_decode(File::get('database/seeders/maps/nyss.json'));

        /**
         * get senate geographies from geojson file
         */
        
        
        $senate_id = $district_type->select('id')->where('type', 'senate')->first()->id;
        
        foreach($ss_map->features as $feature):
        
            $district->create([
                'district_type_id' => $senate_id,
                'number' => $feature->properties->StSenDist,
                'geo_type' => District::validateGeoType($feature->geometry->type),
                'polygon' => $feature->geometry->type === 'Polygon' ? GeoJSON::createPolygon($feature->geometry->coordinates) : null,
                'multipolygon' => $feature->geometry->type === 'MultiPolygon' ? GeoJSON::createMultiPolygon($feature->geometry->coordinates) : null,
            ]);
            
        endforeach;

        $cc_map = json_decode(File::get('database/seeders/maps/nycc.json'));
       
        
        /**
         * get council geographies from geojson file
         */
       
        $council_id = $district_type->select('id')->where('type', 'council')->first()->id;
       
        foreach($cc_map->features as $feature):
            
            $district->create([
                'district_type_id' => $council_id,
                'number' => $feature->properties->CounDist,
                'geo_type' => District::validateGeoType($feature->geometry->type),
                'polygon' => $feature->geometry->type === 'Polygon' ? GeoJSON::createPolygon($feature->geometry->coordinates) : null,
                'multipolygon' => $feature->geometry->type === 'MultiPolygon' ? GeoJSON::createMultiPolygon($feature->geometry->coordinates) : null,
            ]);
            

        endforeach;
        
    }
}
