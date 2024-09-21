<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssemblyHousing extends Model
{
    use HasFactory;

    protected $table = 'assembly_housing';

    protected $fillable = [
        'assemblydistrict',
        'units',
        'relevance_start',
        'relevance_end',
        'source'
    ];

    
}
