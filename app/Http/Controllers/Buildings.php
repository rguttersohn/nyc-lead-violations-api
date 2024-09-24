<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Building;
use App\Support\PostGIS;
use Illuminate\Support\Carbon;

class Buildings extends Controller
{
    public function getBuilding(Request $request, $nyc_open_data_building_id){

        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));

        $start_formatted = "$start_year-01-01";
        $end_formatted = "$end_year-12-31";

        return Building::select('nyc_open_data_building_id', 'bin', 'address', 'zip', 'sd.senatedistrict as senate','ad.assemblydistrict as assembly', 'cd.councildistrict as council', 'point')
            ->selectRaw('
                AVG(violations.currentstatusdate - violations.inspectiondate) FILTER(WHERE violations.currentstatusid = 19) as avg_days_before_closed,
                AVG(CURRENT_DATE - violations.inspectiondate) FILTER(WHERE violations.currentstatusid != 19) as avg_days_open
                ')
        // eager load violations
            ->with('violations', function($query)use($start_formatted, $end_formatted){
                $query->select('nyc_open_data_violation_id','apartment','building_id','codes.ordernumber','codes.definition', 'inspectiondate', 'currentstatusdate','currentstatusid')
                    ->selectRaw('CASE WHEN currentstatusid = 19 THEN currentstatusdate - inspectiondate ELSE CURRENT_DATE - inspectiondate END as days_open')
                    ->join('codes', 'codes.ordernumber', 'violations.ordernumber')
                    ->where([['violations.inspectiondate', '>=', $start_formatted],['violations.inspectiondate', '<=', $end_formatted]])
                    ;
            })
            // join violations
            ->join('violations', function($join)use($start_formatted, $end_formatted){
                $join->on('violations.building_id', 'buildings.nyc_open_data_building_id')
                    ->where([['violations.inspectiondate','>=',$start_formatted], ['violations.inspectiondate','<=', $end_formatted]]);
            })
            // join senate district
            ->join('senate_districts as sd', function($join){
                $join->on(...PostGIS::createSpatialJoin('buildings.point', 'sd.polygon'))
                    ->orOn(...PostGis::createSpatialJoin('buildings.point', 'sd.multipolygon'));
            })
            // join assembly district
            ->join('assembly_districts as ad', function($join){
                $join->on(...PostGIS::createSpatialJoin('buildings.point', 'ad.polygon'))
                    ->orOn(...PostGis::createSpatialJoin('buildings.point', 'ad.multipolygon'));
            })
            // join council district
            ->join('council_districts as cd', function($join){
                $join->on(...PostGIS::createSpatialJoin('buildings.point', 'cd.polygon'))
                    ->orOn(...PostGis::createSpatialJoin('buildings.point', 'cd.multipolygon'));
            })
            ->where('nyc_open_data_building_id', $nyc_open_data_building_id)
            ->groupBy('nyc_open_data_building_id', 'bin', 'address', 'zip', 'senate','assembly', 'council', 'point')
            ->get()->toArray();        
    }
}
