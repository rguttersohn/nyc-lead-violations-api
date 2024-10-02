<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use MatanYadaev\EloquentSpatial\Objects\Point;

class PostGIS {

    public static function createSpatialJoin(string $table_name, string $point, string $first_geometry, string $second_geometry, string $boolean = "true"):array {
        
        return [DB::raw("ST_within($point, CASE WHEN $table_name.geo_type = $first_geometry THEN $table_name.$first_geometry WHEN $table_name.geo_type = $second_geometry THEN $table_name.$second_geometry END)"), "=", DB::raw("$boolean")];
    }

    public static function simplifyGeoJSON(string $table, string $geometry, float $tolerance):string{

        return ("St_asgeojson(ST_simplify($table.$geometry, $tolerance)) as $geometry");
    }

    public static function getGeoJSON(string $table, string $geometry):string{
        return ("ST_asgeojson($table.$geometry) as $geometry");
    }

    public static function createPolygon (array $coordinates){

        $polygon = new Polygon(
            array_map(function($coordinate){
                return new LineString(
                    array_map(function($points){
                        return new Point($points[1],$points[0]);
                    }, $coordinate)
                );
            }, $coordinates),
            Srid::WGS84->value
        );

        return $polygon;
    }

    public static function createMultiPolygon (array $polygons){

        $multipolygon = new MultiPolygon(
            array_map(function($polygon){
                return new Polygon(
                    array_map(function($coordinates){
                        return new LineString(
                            array_map(function($points){
                                return new Point($points[1],$points[0], Srid::WGS84->value);
                            }, $coordinates)
                        );
                    }, $polygon),
                    Srid::WGS84->value
                );
            },$polygons),
            Srid::WGS84->value
        );

        return $multipolygon;
        
    }

}