<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Traits\ViolationQueries;
use App\Http\Controllers\Traits\ValidateQueryParams;

class District extends Controller
{
    use ViolationQueries, ValidateQueryParams;

    public function getSenateDataWithID(Request $request, $district_id){

        $uri = $request->path();
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);
       
        return $this->getDistrictViolationsWithID('senate_districts', $district_id, $uri, $valid_status, $start_year, $end_year, $status_needs_checking );
    }

    public function getAssemblyDataWithID(Request $request, $district_id):array{

        $uri = $request->path();
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);
       
        return $this->getDistrictViolationsWithID('assembly_districts', $district_id, $uri, $valid_status, $start_year, $end_year, $status_needs_checking );
    }

    public function getCouncilDataWithID(Request $request, $district_id):array{

        $uri = $request->path();
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);
       
        return $this->getDistrictViolationsWithID('council_districts', $district_id, $uri, $valid_status, $start_year, $end_year, $status_needs_checking );

    }  


}
