<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ValidateQueryParams;
use App\Http\Controllers\Traits\ViolationQueries;
use App\Support\GeoJSON;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class Districts extends Controller
{
    use ViolationQueries, ValidateQueryParams;
    
    public function getSenateData(Request $request){


        $uri = $request->path();
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);
        $data = $this->queryDistrictViolations('senate_districts', $uri, $valid_status, $start_year, $end_year, status_needs_checking: $status_needs_checking);
       
        $geojson = GeoJSON::getGeoJSON($data, ['start_year','end_year','status','district_type','district', 'violations']);

        return $geojson;

    }

    public function getAssemblyData(Request $request){

        $uri = $request->path();
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);

        $data = $this->queryDistrictViolations('assembly_districts', $uri, $status, $start_year, $end_year, status_needs_checking:$status_needs_checking);
       
        $geojson = GeoJSON::getGeoJSON($data, ['start_year','end_year','status','district_type', 'district', 'violations']);

        return $geojson;

    }

    public function getCouncilData(Request $request){

        $uri = $request->path();
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);
        $data = $this->queryDistrictViolations('council_districts', $uri, $status, $start_year, $end_year, status_needs_checking: $status_needs_checking);
       
        $geojson = GeoJSON::getGeoJSON($data, ['start_year','end_year','status','start_year','district_type','district', 'violations']);

        return $geojson;

    }

    public function getDistrictData(Request $request){

        $uri = $request->path();
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);

        $data = $this->queryDistrictViolations();

    }
}
