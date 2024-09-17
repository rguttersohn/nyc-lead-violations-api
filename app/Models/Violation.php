<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Violation extends Model
{
    use HasFactory;

    protected $table = 'violations';
    
    protected $fillable = [
        'ordernumber' ,
        'inspectiondate' ,
        'currentstatusdate',
        'nyc_open_data_violation_id',
        'apartment',
        'building_id',
        'currentstatusid'
    ];

    protected function casts(){

        return [
            'nyc_open_data_violation_id' => 'integer',
            'ordernumber' => 'integer',
            'currentstatusdate' => 'timestamp',
            'inspectiondate' => 'timestamp',
            'building_id' => 'integer'
        ];
    }

    public function buildings():BelongsTo{
        return $this->belongsTo(Building::class,'building_id','nyc_open_data_building_id');
    }
    
}
