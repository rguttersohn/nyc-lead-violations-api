<?php

namespace App\Support;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use stdClass;


class GeoJSON {

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

    public static function convert(array $center, int $radius, $numberOfSegments)
    {
        $n = $numberOfSegments;
        $flatCoordinates = [];
        for ($i = 0; $i < $n; $i++) {
            $bearing = 2 * 3.14159 * $i / $n;
            $flatCoordinates[] = self::offset($center, $radius, $bearing);
        }

        return [
            'type' => 'Polygon',
            'coordinates' => [$flatCoordinates]
        
        ];
    }

    public static function offset($center, $distance, $bearing) {
        $lat1 = deg2rad($center[1]);
        $lon1 = deg2rad($center[0]);
        $dByR = $distance/6378137; // convert dist to angular distance in radians
    
        $lat = asin(
            sin($lat1) * cos($dByR) + 
                cos($lat1) * sin($dByR) * cos($bearing)
        );
        $lon = $lon1 + atan2(
            sin($bearing) * sin($dByR) * cos($lat1),
            cos($dByR) - sin($lat1) * sin($lat)
        );
        $lon = fmod(
            $lon + 3 * 3.14159,
            2 * 3.14159
        ) - 3.14159;
        return [round(rad2deg($lon), 6), round(rad2deg($lat), 6)];
    }


     /**
     * @param array<int, string> $properties; @def key: any properties you want added to your geojson features
     * 
     * @param stdClass data_query_result_object; @def a subset of the data resulting from the data query;
     */
    public static function getGeoJSONProperties($properties, $data_query_result_object):array{

        $properties_array = [];

        foreach($properties as $property):
           
            if($property === 'data'):
                $property_float = floatval($data_query_result_object->{$property});

                $properties_array[$property] = $property_float;

                continue;
            
            endif;

            $properties_array[$property] = $data_query_result_object->{"$property"};
        
        endforeach;

        return $properties_array;
    }

    /**
     * 
     * @param [] $data_query_result
     * @param string[] $properties
     * @param strng $geo_type_defining_key
     * a key in the data query result that defines the geo type of the features. Default is 'geo_type'
     */
    
    public static function getGeoJSON(array $data_query_result, array $properties, string $geo_type_defining_key = 'geo_type' ):array{

        return [
            'type' => 'FeatureCollection',
            'features' => array_map(function(stdClass $d)use($properties, $geo_type_defining_key){
    
                return [
                    'type' => 'Feature', 
                    'geometry' => isset($d->{$d->{$geo_type_defining_key}}) ? json_decode($d->{$d->{$geo_type_defining_key}}) : null,
                    'properties' => self::getGeoJSONProperties($properties, $d)
                ];
            }, $data_query_result)
        ];
    }

    public static function get3DGeoJson(array $data_query_result, array $properties, string $geo_type_defining_key = 'geo_type'){
        
        $violations_geojson = [
            'type' => 'FeatureCollection',
        ];
        
        $violations_geojson['features'] = array_map(function($d)use($properties, $geo_type_defining_key){
            
            $coordinates = json_decode($d->point)->coordinates;

            
            
            return [
              'type' => 'Feature',
              'geometry' => !$coordinates ? [
                'type' => 'Point',
                'coordinates' => [[]]
              ] : self::convert([...$coordinates], 15, 4),
              'properties' => self::getGeoJSONProperties($properties, $d)
            ];

          }, $data_query_result);

        return $violations_geojson;
    }
}