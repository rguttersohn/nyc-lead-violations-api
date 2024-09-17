<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Support\CacheKey;
use App\Support\PostGIS;

trait ViolationQueries {

    
    private function formatDistrictSelectionName($string) {
      // Remove all underscores
      $string = str_replace('_', '', $string);
      
      // Remove the last 's' by finding its position and slicing the string
      $lastSPos = strrpos($string, 's');
      if ($lastSPos !== false) {
          $string = substr_replace($string, '', $lastSPos, 1);
      }
      
      return $string;
    }

    private function queryViolations(string $uri, string $status, string $start_year, string $end_year):array{

        $start_formatted = "$start_year-01-01";
        $end_formatted = "$end_year-12-31";
        $cache_key = CacheKey::generateQueryKey($uri, $status, $start_year, $end_year);

        $data = DB::table('buildings as b')
          ->selectRaw('b.address,b.geo_type, b.bin, b.nyc_open_data_building_id, st_asgeojson(point) as point, count(v.nyc_open_data_violation_id) as violations',)
          ->join('violations as v', 'v.building_id', 'b.nyc_open_data_building_id')
          ->groupBy('b.address','point','b.geo_type','b.bin', 'b.nyc_open_data_building_id')
          ->where([['v.inspectiondate', '>=', $start_formatted],['v.inspectiondate', '<=', $end_formatted], ['b.point', '!=', null]])
          ->get()->toArray();

        return $data;

    }

    private function queryDistrictViolations(string $table_name, string $uri, string $status, string $start_year, string $end_year, bool $status_needs_checking = false){

      $start_formatted = "$start_year-01-01";
      $end_formatted = "$end_year-12-31";
      $district_selection_name = $this->formatDistrictSelectionName($table_name);

      $cache_key = CacheKey::generateQueryKey($uri, $status, $start_year, $end_year);

      return DB::table("$table_name")
            ->selectRaw("'$start_year' as start_year,'$end_year' as end_year, '$status' as status, '$district_selection_name' as district_type, $district_selection_name as district, $table_name.geo_type, st_asgeojson(polygon) as polygon, st_asgeojson(multipolygon) multipolygon, coalesce(count(v.nyc_open_data_violation_id),0) as violations")
            ->leftJoin('buildings as b', function($join){
                $join->on(...PostGIS::createSpatialJoin('b.point', 'polygon'))
                    ->orOn(...PostGIS::createSpatialJoin('b.point', 'multipolygon'));
            })
            ->leftJoin('violations as v', function($join)use($start_formatted, $end_formatted, $status_needs_checking, $status){
              $join->on('v.building_id', 'b.nyc_open_data_building_id')
                  ->where([['v.inspectiondate', '>=', $start_formatted],['v.inspectiondate', '<=', $end_formatted], ['b.point', '!=', null]])
                  ->when($status_needs_checking, function($query)use($status){
                    if($status === 'open'):
                      
                      $query->where('v.currentstatusid', "!=", 19);
                    
                    else:
                      
                      $query->where('v.currentstatusid', 19);
                    
                    endif;
                });
            })
            ->groupBy('start_year', 'end_year', 'status', 'district_type','district', "$table_name.geo_type",'polygon','multipolygon')
            ->get()->toArray();
    }

    private function getDistrictViolationsWithID(string $table_name, string $district_id, string $uri, string $status, string $start_year, string $end_year, bool $status_needs_checking = false){


      $start_formatted = "$start_year-01-01";
      $end_formatted = "$end_year-12-31";

      $district_selection_name = $this->formatDistrictSelectionName($table_name);
      $cache_key = CacheKey::generateQueryKey($uri, $status, $start_year, $end_year);

      
      return DB::table("$table_name")
        ->selectRaw("'$start_year' as start_year,'$end_year' as end_year, '$status' as status, $table_name.$district_selection_name as district, COALESCE(COUNT(v.*),0) as violations")
        ->where("$table_name.$district_selection_name", $district_id)
        ->leftjoin('buildings as b', function($join)use($table_name){
            $join->on(...PostGIS::createSpatialJoin('b.point',"$table_name.polygon"))
                ->orOn(...PostGIS::createSpatialJoin('b.point', "$table_name.multipolygon"));
        })
        ->leftjoin('violations as v', function($join)use($start_formatted, $end_formatted){
            $join->on('b.nyc_open_data_building_id', 'v.building_id')
                ->where([['v.inspectiondate', '>=', $start_formatted],['v.inspectiondate', '<=', $end_formatted], ['b.point', '!=', null]]);
        })
        ->groupBy('start_year','end_year','status','district')
        ->get()->toArray();
    } 
}