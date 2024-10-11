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
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);

        $start_formatted = $this->getFormattedStartYear($start_year);
        $end_formatted = $this->getFormattedEndYear($end_year);

        $district_type = DistrictType::currentDistrictType($district_type)->first();

        if(!$district_type):
            return response(['error'=>'district id not found'], 400);
        endif;
        
        return District::selectRaw("EXTRACT(year FROM v.inspectiondate) as year")
            ->selectRaw('COUNT(v.*) as violations')
            ->joinBuildings()
            ->join('violations as v', function($join)use($start_formatted, $end_formatted){
                $join->on('v.building_id', 'b.nyc_open_data_building_id')
                    ->where([["v.inspectiondate", ">=", "$start_formatted"], ["v.inspectiondate", "<=", $end_formatted]]);
            })
            ->where([['district_type_id', $district_type->id], ['number', $disrict_id]])
            ->groupBy(DB::raw("EXTRACT(year FROM v.inspectiondate)"))
            ->orderBy('year', 'ASC')
            ->get();
        
    }

    public function getBuildingTimeline(Request $request, $id){

        $uri = $request->path();

        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);

        $start_formatted = $this->getFormattedStartYear($start_year);
        $end_formatted = $this->getFormattedEndYear($end_year);        
        
        return Building::selectRaw("EXTRACT(year FROM v.inspectiondate) as year")
            ->selectRaw('COUNT(v.*) as violations')
            ->join('violations as v', function($join)use($start_formatted, $end_formatted){
                $join->on('v.building_id', 'buildings.nyc_open_data_building_id')
                ->where([["v.inspectiondate", ">=", "$start_formatted"], ["v.inspectiondate", "<=", $end_formatted]]);
            })
            ->groupBy(DB::raw("EXTRACT(year FROM v.inspectiondate)"))
            ->where('buildings.nyc_open_data_building_id', $id)
            ->orderBy('year','ASC')
            ->get();



    }
}
