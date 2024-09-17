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
        'address',
        'longlat',
        'zip',
        'nyc_open_data_building_id',
    ];

    protected $attributes = [
        'geo_type' => 'point',
    ];


    protected function casts (){

        return [
            'longlat' => Point::class,
            'nyc_open_data_building_id' => 'integer',
            'bin' => 'integer',
            'councildistrict' => 'integer',
        ];
    }

    public function violations():HasMany{
        return $this->hasMany(Violation::class, 'building_id', 'nyc_open_data_building_id');
    }
}
