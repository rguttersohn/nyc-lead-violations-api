<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ValidateQueryParams;
use App\Support\GeoJSON;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\DistrictType;
use App\Support\PostGIS;
use App\Models\District;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class Districts extends Controller
{
    use ValidateQueryParams;
    
    public function getAllDistricts(){

        return DistrictType::select('type', 'id')
            ->where('type', '!=', 'uhf')
            ->with('districts', fn($query)=>$query->select('id','number as district', 'district_type_id'))
            ->get();
    }

    public function getDistrictData(Request $request, $district_type){

        $uri = $request->path();
        
        // status
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);

        //code
        $code = $request->query('code', 'all');
        $valid_code = $this->getValidCodeQuery($code);
        $code_needs_filtering = $this->codeNeedsFiltering($valid_code);
        
        //years
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $start_formatted = $this->getFormattedStartYear($start_year);
        $end_formatted = $this->getFormattedEndYear($end_year);

        $cache_key = CacheKey::generateGeoJsonKey($uri, $status, $start_year, $end_year, '2d');
        
        if(Cache::has($cache_key)):

            return response(Cache::get($cache_key))
                ->header('From-Cache', 'true');

        endif;

        $district_type = DistrictType::currentDistrictType($district_type)->first();
        
        if(!$district_type):
            
            Response::header('from-cache', true);            
            return response(['error'=> 'District ID not found'], 400);
        
        endif;

        $data = District::select('number as district', 'districts.geo_type', 'h.units as total_housing_units', 'h.source as housing_source')
            ->selectRaw("'$district_type->type' as district_type")
            ->selectRaw("'$valid_status' as status")
            ->selectRaw("'$start_year' as start_year")
            ->selectRaw("'$end_year' as end_year")
            ->selectRaw('COALESCE(COUNT(v.*), 0) as violations')
            ->selectRaw(PostGIS::simplifyGeoJSON('districts','polygon', .0003))
            ->selectRaw(PostGIS::simplifyGeoJSON('districts', 'multipolygon', .0003))
            ->selectRaw('COUNT(DISTINCT b.id) FILTER(WHERE b.nyc_open_data_building_id = v.building_id) as buildings_with_violations')
            ->selectRaw('COUNT(DISTINCT (v.building_id, v.apartment)) FILTER(WHERE v.building_id IS NOT NULL) AS units_with_violations')
            ->where('district_type_id', $district_type->id)
            ->joinBuildings('left')
            ->joinViolations($start_formatted, $end_formatted, $status_needs_checking, $status, 'left', $code_needs_filtering, $valid_code)
            ->leftJoin('housing as h', 'h.district_id','=','districts.id')
            ->orderBy('district')
            ->groupBy('district', 'district_type', 'districts.geo_type', 'polygon', 'multipolygon','total_housing_units', 'housing_source')
            ->get();
            
        $geojson = GeoJSON::getGeoJSON($data, ['district', 'district_type','violations', 'status', 'start_year', 'end_year','buildings_with_violations','units_with_violations','total_housing_units', 'housing_source']);

        Cache::put($cache_key, $geojson);
                
        return response($geojson)
            ->header('From-Cache', 'false');

    }

}
