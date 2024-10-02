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
use App\Models\Building;
use App\Support\PostGIS;
use Illuminate\Support\Facades\DB;

class Buildings3D extends Controller
{
    use ViolationQueries, ValidateQueryParams;
   
    public function getBuildings(Request $request){

        $uri = $request->path();
        $start_year = $request->query('start_year', '2024');
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');

        $cache_key = CacheKey::generateGeoJsonKey($uri, $start_year, $end_year, $status, '3d');

        $valid_status = $this->getValidStatusQuery($status);

        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);

        $start_formatted = $this->getFormattedStartYear($start_year);
        $end_formatted = $this->getFormattedEndYear($end_year);

        $data = Building::select('streetname', 'housenumber', 'bin', 'nyc_open_data_building_id', 'geo_type', 'point')
            ->selectRaw('COUNT(v.*) as violations')
            ->selectRaw(PostGIS::getGeoJSON('buildings', 'point'))
            ->joinViolations($start_formatted, $end_formatted, $status_needs_checking, $status)
            ->groupBy('streetname', 'housenumber', 'bin', 'nyc_open_data_building_id', 'point', 'geo_type')
            ->where('buildings.point', '!=', 'null')
            ->get();

        $geojson = GeoJSON::get3DGeoJson($data, ['streetname','housenumber','bin', 'nyc_open_data_building_id', 'violations']);
        
        return $geojson;
  
    }
}

