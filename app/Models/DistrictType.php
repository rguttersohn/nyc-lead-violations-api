<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistrictType extends Model
{
    use HasFactory;

    protected $fillable = [
        'type'
    ];

    public function scopeCurrentDistrictType($query, string $district_type){

        $query->select('type', 'id')->where('type', $district_type);
    }

    public function districts():HasMany{
        return $this->hasMany(District::class);
    }

}
