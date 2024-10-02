<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ValidateQueryParams;
use Illuminate\Http\Request;
use App\Models\Building;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Carbon;


class City extends Controller
{   
    use ValidateQueryParams;

    public function getCityData(Request $request){
        
        $uri = $request->path();
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);

        $start_formatted = $this->getFormattedStartYear($start_year);
        $end_formatted = $this->getFormattedEndYear($end_year);

        return Building::selectRaw("'city' as district_type")
            ->selectRaw('COUNT(v.*) as violations')
            ->selectRaw('COUNT(DISTINCT buildings.id) as buildings_with_violation')
            ->selectRaw('COUNT(DISTINCT (v.building_id, v.apartment)) as units_with_violation')
            ->joinViolations($start_formatted, $end_formatted, $status_needs_checking, $status)
            ->get();
    }
}
