<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MatanYadaev\EloquentSpatial\Objects\Point;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'bin',
        'housenumber',
        'streetname',
        'point',
        'zip',
        'nyc_open_data_building_id',
        'boro'
    ];

    protected $attributes = [
        'geo_type' => 'point',
    ];


    protected function casts (){

        return [
            'point' => 'array',
            'nyc_open_data_building_id' => 'integer',
            'bin' => 'integer',
            'councildistrict' => 'integer',
            'avg_days_open' => 'float',
            'avg_days_before_closed' => 'float'
        ];
    }

    public function violations():HasMany{
        return $this->hasMany(Violation::class, 'building_id', 'nyc_open_data_building_id');
    }

    public function scopeJoinViolations($query, $start_formatted, $end_formatted, $status_needs_checking, $status, $join_type = null, $code_needs_filtering, $code){
        if($join_type === 'left'):

            $query->leftJoin('violations as v', function($join)use($start_formatted, $end_formatted, $status_needs_checking, $status, $code_needs_filtering, $code){
                $join->on('v.building_id', 'buildings.nyc_open_data_building_id')
                    ->where([['v.inspectiondate', '>=', $start_formatted],['v.inspectiondate', '<=', $end_formatted]])
                    ->when($status_needs_checking, function($query)use($status){
    
                      if($status === 'open'):
                        
                        $query->where('v.currentstatusid', "!=", 19);
                      
                      else:
                        
                        $query->where('v.currentstatusid', 19);
                      
                      endif;
                  })->when($code_needs_filtering, function($query) use($code){
                    $query->where('v.ordernumber', $code);
                    });

              });

        else:
            
            $query->join('violations as v', function($join)use($start_formatted, $end_formatted, $status_needs_checking, $status, $code_needs_filtering, $code){
                $join->on('v.building_id', 'buildings.nyc_open_data_building_id')
                    ->where([['v.inspectiondate', '>=', $start_formatted],['v.inspectiondate', '<=', $end_formatted]])
                    ->when($status_needs_checking, function($query)use($status){
    
                      if($status === 'open'):
                        
                        $query->where('v.currentstatusid', "!=", 19);
                      
                      else:
                        
                        $query->where('v.currentstatusid', 19);
                      
                      endif;
                  })->when($code_needs_filtering, function($query) use($code){
                        $query->where('v.ordernumber', $code);
                    });
              });

        endif;
        
    }
}
