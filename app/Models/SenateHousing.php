<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SenateHousing extends Model
{
    use HasFactory;

    protected $table = 'senate_housing';

    protected $fillable = [
        'senatedistrict',
        'units',
        'relevance_start',
        'relevance_end',
        'source'
    ];
}
