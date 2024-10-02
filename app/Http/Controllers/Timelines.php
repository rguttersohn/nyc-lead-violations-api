<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\District;
use App\Models\DistrictType;
use App\Http\Controllers\Traits\ValidateQueryParams;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Timelines extends Controller
{   
    use ValidateQueryParams;

    public function getDistrictTimeline(Request $request, $district_type, $disrict_id){
        
        $uri = $request->path();

        $district_type = DistrictType::currentDistrictType($district_type)->first();

        if(!$district_type):
            return response(['error'=>'district id not found'], 400);
        endif;
        
        return District::selectRaw("EXTRACT(year FROM v.inspectiondate) as year")
            ->selectRaw('COUNT(v.*) as violations')
            ->joinBuildings()
            ->join('violations as v', 'v.building_id', 'b.nyc_open_data_building_id')
            ->where([['district_type_id', $district_type->id], ['number', $disrict_id]])
            ->groupBy(DB::raw("EXTRACT(year FROM v.inspectiondate)"))
            ->get();
        
    }

    public function getBuildingTimeline(Request $request, $id){

        $uri = $request->path();
        
        return Building::selectRaw("EXTRACT(year FROM v.inspectiondate) as year")
            ->selectRaw('COUNT(v.*) as violations')
            ->join('violations as v', 'v.building_id', 'buildings.nyc_open_data_building_id')
            ->groupBy(DB::raw("EXTRACT(year FROM v.inspectiondate)"))
            ->where('buildings.nyc_open_data_building_id', $id)
            ->get();



    }
}
