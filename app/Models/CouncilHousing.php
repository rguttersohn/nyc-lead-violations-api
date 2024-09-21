<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouncilHousing extends Model
{
    use HasFactory;

    protected $table = 'council_housing';

    protected $fillable = [
        'councildistrict',
        'units',
        'relevance_start',
        'relevance_end',
        'source'
    ];
}
