<?php

namespace Database\Seeders;

use App\Models\SenateDistrict;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Support\GeoJSON;


class SenateDistrictsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(SenateDistrict $senate_district): void
    {
        
        $cc_map = json_decode(File::get('database/seeders/maps/nyss.json'));

        /**
         * get geographies from geojson file
         */
        foreach($cc_map->features as $feature):
            
            $senate_district->create([
                'senatedistrict' => $feature->properties->StSenDist,
                'geo_type' => SenateDistrict::validateGeoType($feature->geometry->type),
                'polygon' => $feature->geometry->type === 'Polygon' ? GeoJSON::createPolygon($feature->geometry->coordinates) : null,
                'multipolygon' => $feature->geometry->type === 'MultiPolygon' ? GeoJSON::createMultiPolygon($feature->geometry->coordinates) : null,
            ]);
            
        endforeach;
    }
}
