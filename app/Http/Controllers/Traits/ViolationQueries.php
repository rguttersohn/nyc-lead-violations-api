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


    private function formatDistrictHousingTableName($string) {
      // Remove all underscores
      $string = explode('_', $string);
      
      return $string[0] . '_housing';
    }

    private function queryViolations(string $uri, string $status, string $start_year, string $end_year, bool $status_needs_checking):array{

        $start_formatted = "$start_year-01-01";
        $end_formatted = "$end_year-12-31";
        $cache_key = CacheKey::generateQueryKey($uri, $status, $start_year, $end_year);
                
        $data = DB::table('buildings as b')
          ->selectRaw('b.address,b.geo_type, b.bin, b.nyc_open_data_building_id, st_asgeojson(point) as point, count(v.nyc_open_data_violation_id) as violations',)
          ->join('violations as v', 'v.building_id', 'b.nyc_open_data_building_id')
          ->groupBy('b.address','point','b.geo_type','b.bin', 'b.nyc_open_data_building_id')
          ->where([['v.inspectiondate', '>=', $start_formatted],['v.inspectiondate', '<=', $end_formatted], ['b.point', '!=', null]])
          ->when($status_needs_checking, function($query)use($status){
            
            if($status === 'open'):
              
              $query->where('v.currentstatusid', "!=", 19);
            
            else:
              
              $query->where('v.currentstatusid', 19);
            
            endif;
          })
          ->get()->toArray();

        return $data;

    }

    private function queryDistrictViolations(string $table_name, string $uri, string $status, string $start_year, string $end_year, bool $status_needs_checking = false){

      $start_formatted = "$start_year-01-01";
      $end_formatted = "$end_year-12-31";
      $district_selection_name = $this->formatDistrictSelectionName($table_name);

      $cache_key = CacheKey::generateQueryKey($uri, $status, $start_year, $end_year);
      $housing_table = $this->formatDistrictHousingTableName($table_name);

      return DB::table("$table_name")
            ->selectRaw("'$start_year' as start_year,'$end_year' as end_year, '$status' as status, '$district_selection_name' as district_type, $housing_table.units as total_housing_units, $table_name.$district_selection_name as district, $table_name.geo_type, st_asgeojson(polygon) as polygon, st_asgeojson(multipolygon) as multipolygon, COALESCE(COUNT(v.*),0) as violations, COUNT(DISTINCT b.*) as buildings_with_violations, COUNT(DISTINCT(v.apartment,v.building_id)) as units_with_violations")
            ->leftJoin("$housing_table", "$housing_table.$district_selection_name", "$table_name.$district_selection_name")
            ->leftJoin('buildings as b', function($join){
                $join->on(...PostGIS::createSpatialJoin('b.point', 'polygon'))
                    ->orOn(...PostGIS::createSpatialJoin('b.point', 'multipolygon'));
            })
            ->leftJoin('violations as v', function($join)use($start_formatted, $end_formatted, $status_needs_checking, $status){
              $join->on('v.building_id', 'b.nyc_open_data_building_id')
                  ->where([['v.inspectiondate', '>=', $start_formatted],['v.inspectiondate', '<=', $end_formatted]])
                  ->when($status_needs_checking, function($query)use($status){

                    if($status === 'open'):
                      
                      $query->where('v.currentstatusid', "!=", 19);
                    
                    else:
                      
                      $query->where('v.currentstatusid', 19);
                    
                    endif;
                });
            })
            ->groupBy('start_year', 'end_year', 'status', 'district_type','district', "$table_name.geo_type",'polygon','multipolygon', 'total_housing_units')
            ->get()->toArray();
    }

    private function getDistrictViolationsWithID(string $table_name, string $district_id, string $uri, string $status, string $start_year, string $end_year, bool $status_needs_checking = false){

      $start_formatted = "$start_year-01-01";
      $end_formatted = "$end_year-12-31";

      $district_selection_name = $this->formatDistrictSelectionName($table_name);
      $cache_key = CacheKey::generateQueryKey($uri, $status, $start_year, $end_year);
      
      $housing_table = $this->formatDistrictHousingTableName($table_name);
      return DB::table("$table_name")
        ->selectRaw("'$start_year' as start_year,'$end_year' as end_year, $housing_table.units as total_housing_units, '$status' as status, $table_name.$district_selection_name as district, COALESCE(COUNT(v.*),0) as violations, COUNT(DISTINCT b.*) as buildings_with_violations, COUNT(DISTINCT(v.apartment,v.building_id)) as units_with_violations")
        ->where("$table_name.$district_selection_name", $district_id)
        ->join("$housing_table", "$housing_table.$district_selection_name", "$table_name.$district_selection_name")
        ->join('buildings as b', function($join)use($table_name){
            $join->on(...PostGIS::createSpatialJoin('b.point',"$table_name.polygon"))
                ->orOn(...PostGIS::createSpatialJoin('b.point', "$table_name.multipolygon"));
        })
        ->join('violations as v', function($join)use($start_formatted, $end_formatted, $status_needs_checking, $status){
            $join->on('b.nyc_open_data_building_id', 'v.building_id')
                ->where([['v.inspectiondate', '>=', $start_formatted],['v.inspectiondate', '<=', $end_formatted], ['b.point', '!=', null]])
                ->when($status_needs_checking, function($query)use($status){
                  if($status === 'open'):
                    
                    $query->where('v.currentstatusid', "!=", 19);
                  
                  else:
                    
                    $query->where('v.currentstatusid', 19);
                  
                  endif;
              });
        })
        ->groupBy('start_year','end_year','status','district', 'total_housing_units')
        ->get()->toArray();
    } 
}