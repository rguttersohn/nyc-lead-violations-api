<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Exception;
use App\Support\CacheKey;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Traits\ViolationQueries;
use App\Support\GeoJSON;

class Violations3D extends Controller
{
    use ViolationQueries;
   
    public function getViolations(Request $request){

        $uri = $request->path();
        $start_year = $request->query('start_year', '2024');
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');

        $cache_key = CacheKey::generateGeoJsonKey($uri, $start_year, $end_year, $status, '3d');

        $data = $this->queryViolations($uri, $status, $start_year, $end_year);

        $geojson = GeoJSON::get3DGeoJson($data, ['address','bin', 'nyc_open_data_building_id', 'violations']);
        
        return $geojson;
  
    }
}

