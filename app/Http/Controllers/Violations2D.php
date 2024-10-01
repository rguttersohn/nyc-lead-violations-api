<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ValidateQueryParams;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Support\GeoJSON;
use App\Support\CacheKey;
use App\Http\Controllers\Traits\ViolationQueries;

class Violations2D extends Controller
{
  use ViolationQueries, ValidateQueryParams;

      public function getViolations(Request $request){
        $uri = $request->path();
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);

        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);

        $cache_key = CacheKey::generateGeoJsonKey($uri, $status, $start_year, $end_year, "2d");

        $data = $this->queryViolations($uri, $status, $start_year, $end_year, $status_needs_checking);

        $geojson = GeoJSON::getGeoJSON($data, ['streetname','housenumber','bin', 'nyc_open_data_building_id', 'violations']);

        return $geojson;
      
    }
  
}