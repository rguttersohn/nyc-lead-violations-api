<?php

namespace App\Models;

use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;
use Illuminate\Support\Facades\DB;

class District extends Model
{
    use HasFactory;

    protected $table = 'districts';

    protected $fillable = [
        'district_type_id', 
        'number',
        'geo_type',
        'polygon',
        'multipolygon'
    ];

    protected $casts = [
        'polygon' => 'array',
        'multipolygon' => 'array'
    ];

    public static function validateGeoType(string | null $type):string | null | Exception  {

        return match($type){
            
            'Polygon' => 'polygon',
            'MultiPolygon' => 'multipolygon',
            null => null,
            default => throw new Exception('wrong geotype added')
        
        };
    }

    public function scopeJoinBuildings($query, $join_type = null){
        if($join_type === 'left'):
            $query->leftJoin('buildings as b', function($join) {
                $join->where(DB::raw("ST_Within(b.point, districts.polygon)"), '=', DB::raw('true'))
                     ->orWhere(DB::raw("ST_Within(b.point, districts.multipolygon)"), '=', DB::raw('true'));
            });
        else:
            $query->join('buildings as b', function($join) {
                $join->where(DB::raw("ST_Within(b.point, districts.polygon)"), '=', DB::raw('true'))
                     ->orWhere(DB::raw("ST_Within(b.point, districts.multipolygon)"), '=', DB::raw('true'));
            });
        endif;
       
    }

    public function scopeJoinViolations($query, $start_formatted, $end_formatted, $status_needs_checking, $status, $join_type = null){
        if($join_type === 'left'):

            $query->leftJoin('violations as v', function($join)use($start_formatted, $end_formatted, $status_needs_checking, $status){
                $join->on('v.building_id', 'b.nyc_open_data_building_id')
                    ->where([['v.inspectiondate', '>=', $start_formatted],['v.inspectiondate', '<=', $end_formatted]])
                    ->when($status_needs_checking, function($query)use($status){
    
                      if($status === 'open'):
                        
                        $query->where('v.currentstatusid', "!=", 19);
                      
                      else:
                        
                        $query->where('v.currentstatusid', 19);
                      
                      endif;
                  });
              });

        else:
            
            $query->join('violations as v', function($join)use($start_formatted, $end_formatted, $status_needs_checking, $status){
                $join->on('v.building_id', 'b.nyc_open_data_building_id')
                    ->where([['v.inspectiondate', '>=', $start_formatted],['v.inspectiondate', '<=', $end_formatted]])
                    ->when($status_needs_checking, function($query)use($status){
    
                      if($status === 'open'):
                        
                        $query->where('v.currentstatusid', "!=", 19);
                      
                      else:
                        
                        $query->where('v.currentstatusid', 19);
                      
                      endif;
                  });
              });

        endif;
        
    }

    public function scopeCurrentDistrict($query, $district_id){

        $query->select('number', 'id')->where('id', $district_id);
    }

  
}
