<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ValidateQueryParams;
use Illuminate\Http\Request;
use App\Models\Building;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Carbon;
use App\Models\DistrictType;
use App\Models\District;


class City extends Controller
{   
    use ValidateQueryParams;

    public function getCityData(Request $request){
        
        $uri = $request->path();
        
        //status
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);
        
        //code
        $code = $request->query('code', 'all');
        $valid_code = $this->getValidCodeQuery($code);
        $code_needs_filtering = $this->codeNeedsFiltering($valid_code);

        //years
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $start_formatted = $this->getFormattedStartYear($start_year);
        $end_formatted = $this->getFormattedEndYear($end_year);

        $data = Building::selectRaw("'city' as district_type")
            ->selectRaw('COUNT(v.*) as violations')
            ->selectRaw('COUNT(DISTINCT buildings.id) as buildings_with_violations')
            ->selectRaw('COUNT(DISTINCT (v.building_id, v.apartment)) as units_with_violations')
            ->joinViolations($start_formatted, $end_formatted, $status_needs_checking, $status, null, $code_needs_filtering, $valid_code)
            ->get()->toArray();


        $council_type = DistrictType::select('id')->where('type', 'council')->first();

        $data[0]['units'] = District::selectRaw("SUM(h.units) as units")
            ->join('housing as h', function($join)use($council_type){
                $join->on('h.district_id', 'districts.id')
                    ->where('districts.district_type_id', $council_type->id);
            })
            ->get()->toArray()[0]['units'];

        return $data;
    }
}
