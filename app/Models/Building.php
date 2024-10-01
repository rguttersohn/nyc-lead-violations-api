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
            'point' => Point::class,
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
}
