<?php

namespace App\Models;

use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;

class SenateDistrict extends Model
{
    use HasFactory;

    protected $table = 'senate_districts';

    protected $fillable = [
        'senatedistrict',
        'geo_type', 
        'polygon', 
        'multipolygon'

    ];

    protected $casts = [
        'polygon' => Polygon::class,
        'multipolygon' => MultiPolygon::class
    ];

    public static function validateGeoType(string | null $type):string | null | Exception  {

        return match($type){
            
            'Polygon' => 'polygon',
            'MultiPolygon' => 'multipolygon',
            null => null,
            default => throw new Exception('wrong geotype added')
        
        };
    }
}
