<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    use HasFactory;

    protected $table = 'codes';

    protected $fillable = [
        'ordernumber',
        'definition',
    ];

    protected function casts(){

        return [
            'ordernumber' => 'integer',
        ];
    }

    public function scopeCurrentCode($query, $code){
        $query->where('ordernumber', $code);
    }



}
