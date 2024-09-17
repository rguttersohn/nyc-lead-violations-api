<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Building;
use App\Support\PostGIS;

class Buildings extends Controller
{
    public function getBuilding($nyc_open_data_building_id){

        return Building::select('nyc_open_data_building_id', 'bin', 'address', 'zip', 'sd.senatedistrict','ad.assemblydistrict', 'cd.councildistrict')
            // join violations
            ->with('violations', function($query){
                $query->select('nyc_open_data_violation_id','apartment','building_id','codes.ordernumber','codes.definition', 'inspectiondate', 'currentstatusdate','currentstatusid')
                    ->join('codes', 'codes.ordernumber', 'violations.ordernumber');
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
            ->get()->toArray();        
    }
}
