<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Building as BuildingModel;
use App\Support\PostGIS;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Building extends Controller
{
    public function getBuilding(Request $request, $id){

        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));

        $start_formatted = "$start_year-01-01";
        $end_formatted = "$end_year-12-31";

        $query_using = $request->query('query_using', 'nyc_open_data_building_id');

        return BuildingModel::select('nyc_open_data_building_id', 'bin', 'streetname', 'housenumber')
            ->selectRaw('
                AVG(violations.currentstatusdate - violations.inspectiondate) FILTER(WHERE violations.currentstatusid = 19) as avg_days_before_closed,
                AVG(CURRENT_DATE - violations.inspectiondate) FILTER(WHERE violations.currentstatusid != 19) as avg_days_open
                ')
            ->selectRaw(PostGIS::getGeoJSON('buildings', 'point'))
            ->selectRaw("SUM(DISTINCT CASE WHEN dt.type = 'senate' THEN d.number ELSE 0 END) AS senate")
            ->selectRaw("SUM(DISTINCT CASE WHEN dt.type = 'assembly' THEN d.number ELSE 0 END) AS assembly")
            ->selectRaw("SUM(DISTINCT CASE WHEN dt.type = 'council' THEN d.number ELSE 0 END) AS council")
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
            // join district
            ->join('districts as d', function($join){ 
                $join->on(DB::raw("ST_within(buildings.point, CASE WHEN d.geo_type = 'polygon' THEN d.polygon WHEN d.geo_type = 'multipolygon' THEN d.multipolygon ELSE NULL END)"), '=', DB::raw('true'));
            })
            ->join('district_types as dt','dt.id', 'd.district_type_id')
            ->where($query_using, $id)
            ->groupBy('nyc_open_data_building_id', 'bin', 'streetname','housenumber', 'point')
            ->get()->toArray();        
    }
}
