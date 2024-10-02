<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ValidateQueryParams;
use App\Support\GeoJSON;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\DistrictType;
use App\Support\PostGIS;
use App\Models\District;

class Districts extends Controller
{
    use ValidateQueryParams;
    
    public function getAllDistricts(){

        return DistrictType::select('type', 'id')->get()->toArray();
    }

    public function getDistrictData(Request $request, $district_type){

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
            
            return response(['error'=> 'District ID not found'], 400);
        
        endif;

        $data = District::select('number as district', 'districts.geo_type')
            ->selectRaw("'$district_type->type' as district_type")
            ->selectRaw("'$valid_status' as status")
            ->selectRaw("'$start_year' as start_year")
            ->selectRaw("'$end_year' as end_year")
            ->selectRaw('COALESCE(COUNT(v.*), 0) as violations')
            ->selectRaw(PostGIS::simplifyGeoJSON('districts','polygon', .0002))
            ->selectRaw(PostGIS::simplifyGeoJSON('districts', 'multipolygon', .0002))
            ->where('district_type_id', $district_type->id)
            ->joinBuildings('left')
            ->joinViolations($start_formatted, $end_formatted, $status_needs_checking, $status, 'left')
            ->orderBy('district')
            ->groupBy('district', 'district_type', 'districts.geo_type', 'polygon', 'multipolygon')
            ->get();
        
        $geojson = GeoJSON::getGeoJSON($data, ['district', 'district_type','violations', 'status', 'start_year', 'end_year']);

        return $geojson;

    }
}
