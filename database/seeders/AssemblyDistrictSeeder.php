<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Support\GeoJSON;
use App\Models\AssemblyDistrict;

class AssemblyDistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(AssemblyDistrict $assembly_district): void
    {
        
        $cc_map = json_decode(File::get('database/seeders/maps/nyad.json'));

        /**
         * get geographies from geojson file
         */
        foreach($cc_map->features as $feature):
            
            $assembly_district->create([
                'assemblydistrict' => $feature->properties->AssemDist,
                'geo_type' => AssemblyDistrict::validateGeoType($feature->geometry->type),
                'polygon' => $feature->geometry->type === 'Polygon' ? GeoJSON::createPolygon($feature->geometry->coordinates) : null,
                'multipolygon' => $feature->geometry->type === 'MultiPolygon' ? GeoJSON::createMultiPolygon($feature->geometry->coordinates) : null,
            ]);
            
        endforeach;

    }
}

