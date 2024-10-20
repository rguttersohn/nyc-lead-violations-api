<?php

namespace App\Http\Controllers;

use App\Models\ScheduleRun;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class UpdatedOn extends Controller
{
    public function getUpdatedOn(){

        if(Cache::has('updated_on')){
            return response(Cache::get('updated_on'))
                ->header('From-Cache', 'true');
        }

        $last_update = ScheduleRun::select('completed_on')->where('success', true)->first();
        $last_update_formatted = ['updated_on' => Carbon::parse($last_update->completed_on)->format('F j, Y')];

        Cache::put('updated_on', $last_update_formatted);
        
        return response($last_update_formatted)
            ->header('From-Cache','false');
    }
}
