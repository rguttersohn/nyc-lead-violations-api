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
            'currentstatusdate' => 'date:F d, Y',
            'inspectiondate' => 'date:F d, Y',
            'building_id' => 'integer'
        ];
    }

    protected static function booted()
    {
        static::created(function ($model) {
            // Clear cache after model is created
            Cache::clear();
        });

        static::updated(function ($model) {
            // Clear cache after model is updated
            Cache::clear();
        });
    }

    public function buildings():BelongsTo{
        return $this->belongsTo(Building::class,'building_id','nyc_open_data_building_id');
    }

    public function status():BelongsTo{
        return $this->belongsTo(Status::class, 'currentstatusid', 'currentstatusid');
    }

}
