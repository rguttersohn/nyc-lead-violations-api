<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ValidateQueryParams;
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
    use ViolationQueries, ValidateQueryParams;
   
    public function getViolations(Request $request){

        $uri = $request->path();
        $start_year = $request->query('start_year', '2024');
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');

        $cache_key = CacheKey::generateGeoJsonKey($uri, $start_year, $end_year, $status, '3d');

        $valid_status = $this->getValidStatusQuery($status);

        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);

        $data = $this->queryViolations($uri, $status, $start_year, $end_year, $status_needs_checking);

        $geojson = GeoJSON::get3DGeoJson($data, ['streetname','housenumber','bin', 'nyc_open_data_building_id', 'violations']);
        
        return $geojson;
  
    }
}

