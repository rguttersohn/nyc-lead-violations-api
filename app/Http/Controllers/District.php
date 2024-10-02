<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Traits\ValidateQueryParams;
use App\Models\DistrictType;
use App\Models\District as DistrictModel;


class District extends Controller
{
    use ValidateQueryParams;

    public function getDistrictDataWithID(Request $request, $district_type, $district_id){

        $uri = $request->path();
        $start_year = $request->query('start_year', Carbon::now('edt')->format('Y'));
        $end_year = $request->query('end_year', Carbon::now('edt')->format('Y'));
        $status = $request->query('status', 'all');
        $valid_status = $this->getValidStatusQuery($status);
        $status_needs_checking = $this->statusNeedsToBeChecked($valid_status);

        $start_formatted = $this->getFormattedStartYear($start_year);
        $end_formatted = $this->getFormattedEndYear($end_year);

        $current_district_type = DistrictType::currentDistrictType($district_type)->first();

        if(!$current_district_type):
            return response(['error'=>'district id not found'], 400);
        endif;

        return DistrictModel::select('districts.id','number as district', 'h.units as total_housing_units', 'h.source as housing_source')
            ->selectRaw("'$current_district_type->type' as district_type")
            ->selectRaw("'$valid_status' as status")
            ->selectRaw("'$start_year' as start_year")
            ->selectRaw("'$end_year' as end_year")
            ->selectRaw('COUNT(DISTINCT b.id) as buildings_with_violations')
            ->selectRaw('COUNT(v.*) as violations')
            ->selectRaw('COUNT(DISTINCT (v.building_id, v.apartment)) as units_with_violations')
            ->joinBuildings()
            ->joinViolations($start_formatted, $end_formatted, $status_needs_checking, $status)
            ->join('housing as h', 'h.district_id','=','districts.id')
            ->where('district_type_id', $district_type)
            ->where('number', $district_id)
            ->groupBy('district', 'district_type','districts.id','total_housing_units', 'housing_source')
            ->get();
    }
}
