<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\District;
use App\Models\DistrictType;
use App\Http\Controllers\Traits\ValidateQueryParams;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Support\CacheKey;

class Timelines extends Controller
{   
    use ValidateQueryParams;

    public function getDistrictTimeline(Request $request, $district_type, $district_id){
        
        $uri = $request->path();
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);

        $start_formatted = $this->getFormattedStartYear($start_year);
        $end_formatted = $this->getFormattedEndYear($end_year);

        $cache_key = CacheKey::generateSubsetKey($uri, $status, $start_year, $end_year, $district_id);
        
        if(Cache::has($cache_key)):
            return response(Cache::get($cache_key))
                ->header('From-Cache', 'true');
        endif;

        $district_type = DistrictType::currentDistrictType($district_type)->first();

        if(!$district_type):
            return response(['error'=>'district id not found'], 400);
        endif;
        
        $data = District::selectRaw("EXTRACT(year FROM v.inspectiondate) as year")
            ->selectRaw('COUNT(v.*) as violations')
            ->joinBuildings()
            ->join('violations as v', function($join)use($start_formatted, $end_formatted){
                $join->on('v.building_id', 'b.nyc_open_data_building_id')
                    ->where([["v.inspectiondate", ">=", "$start_formatted"], ["v.inspectiondate", "<=", $end_formatted]]);
            })
            ->where([['district_type_id', $district_type->id], ['number', $district_id]])
            ->groupBy(DB::raw("EXTRACT(year FROM v.inspectiondate)"))
            ->orderBy('year', 'ASC')
            ->get();

        Cache::put($cache_key, $data);

        return response($data)
            ->header('From-Cache', 'false');
        
    }

    public function getBuildingTimeline(Request $request, $id){

        $uri = $request->path();

        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');

        $start_formatted = $this->getFormattedStartYear($start_year);
        $end_formatted = $this->getFormattedEndYear($end_year);
        
        $cache_key = CacheKey::generateSubsetKey($uri, $status, $start_year, $end_year, $id);
        
        if(Cache::has($cache_key)):
            return response(Cache::get($cache_key))
                ->header('From-Cache', 'true');
        endif;
        
        $data = Building::selectRaw("EXTRACT(year FROM v.inspectiondate) as year")
            ->selectRaw('COUNT(v.*) as violations')
            ->join('violations as v', function($join)use($start_formatted, $end_formatted){
                $join->on('v.building_id', 'buildings.nyc_open_data_building_id')
                ->where([["v.inspectiondate", ">=", "$start_formatted"], ["v.inspectiondate", "<=", $end_formatted]]);
            })
            ->groupBy(DB::raw("EXTRACT(year FROM v.inspectiondate)"))
            ->where('buildings.nyc_open_data_building_id', $id)
            ->orderBy('year','ASC')
            ->get();

        Cache::put($cache_key, $data);

        return response($data)
            ->header('From-Cache', 'false');

    }
}
