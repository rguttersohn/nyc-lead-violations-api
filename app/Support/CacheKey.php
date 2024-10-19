<?php

namespace App\Support;

class CacheKey {

    public static function generateGeoJsonKey(string $uri, string $status, $start_year, $end_year, $dimension ):string{

        return "$uri:$status:$start_year:$end_year:geojson:$dimension";

    }

    public static function generateQueryKey($uri, $status, $start_year, $end_year):string{
        
        return "$uri:$status:$start_year:$end_year";
        
    }

    public static function generateSubsetKey($uri, $status, $start_year,$end_year, $id){

        return "$uri:$status:$start_year:$end_year:$id";
    }
}