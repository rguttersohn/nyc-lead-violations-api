<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ValidateQueryParams;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use App\Support\GeoJSON;
use App\Support\CacheKey;
use App\Models\Building;
use App\Support\PostGIS;

class Buildings2D extends Controller
{
  use ValidateQueryParams;

      public function getBuildings(Request $request){
        $uri = $request->path();
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        
        $start_formatted = $this->getFormattedStartYear($start_year);
        $end_formatted = $this->getFormattedEndYear($end_year);

        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);

        $cache_key = CacheKey::generateGeoJsonKey($uri, $status, $start_year, $end_year, "2d");

        $data = Building::select('streetname', 'housenumber', 'bin', 'nyc_open_data_building_id', 'geo_type', 'point')
          ->selectRaw('COUNT(v.*) as violations')
          ->selectRaw(PostGIS::getGeoJSON('buildings', 'point'))
          ->joinViolations($start_formatted, $end_formatted, $status_needs_checking, $status)
          ->groupBy('streetname', 'housenumber', 'bin', 'nyc_open_data_building_id', 'point', 'geo_type')
          ->where('buildings.point', '!=', 'null')
          ->get();

        $geojson = GeoJSON::getGeoJSON($data, ['streetname','housenumber','bin', 'nyc_open_data_building_id', 'violations']);

        return $geojson;
      
    }
  
}