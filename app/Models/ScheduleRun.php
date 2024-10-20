<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'completed_on',
        'success'
    ];

   
    public function scopeLastSuccessfulUpdate($query){

        $query->select('completed_on')->where('success', true)->orderBy('completed_on')->get();
    }

    
}
