<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ValidateQueryParams;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Support\CacheKey;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Support\GeoJSON;
use App\Models\Building;
use App\Support\PostGIS;

class Buildings3D extends Controller
{
    use ValidateQueryParams;
   
    public function getBuildings(Request $request){

        $uri = $request->path();
      
        //status
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);

         //code
         $code = $request->query('code', 'all');
         $valid_code = $this->getValidCodeQuery($code);
         $code_needs_filtering = $this->codeNeedsFiltering($valid_code);
        
        //years
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $start_year = $request->query('start_year', '2024');
        $start_formatted = $this->getFormattedStartYear($start_year);
        $end_formatted = $this->getFormattedEndYear($end_year);

        $cache_key = CacheKey::generateGeoJsonKey($uri, $start_year, $end_year, $status, '3d');
        
        if(Cache::has($cache_key)):
            
            return response(Cache::get($cache_key))
                    ->header('From-Cache', 'true');

        endif;

        $data = Building::select('streetname', 'housenumber', 'bin', 'nyc_open_data_building_id', 'geo_type', 'point')
            ->selectRaw('COUNT(v.*) as violations')
            ->selectRaw(PostGIS::getGeoJSON('buildings', 'point'))
            ->joinViolations($start_formatted, $end_formatted, $status_needs_checking, $status, null, $code_needs_filtering, $code)
            ->groupBy('streetname', 'housenumber', 'bin', 'nyc_open_data_building_id', 'point', 'geo_type')
            ->where('buildings.point', '!=', 'null')
            ->get();

        $geojson = GeoJSON::get3DGeoJson($data, ['streetname','housenumber','bin', 'nyc_open_data_building_id', 'violations']);
        
        Cache::put($cache_key, $geojson);

        return response($geojson)
            ->header('From-Cache', 'false');
  
    }
}

