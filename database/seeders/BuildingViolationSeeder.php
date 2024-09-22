<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\OpenDataQueries;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use App\Models\Violation;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use App\Models\Building;
use App\Models\ScheduleRun;
use Illuminate\Support\Carbon;

class BuildingViolationSeeder extends Seeder
{

    private int $limit = 10000;
    
    private int $count;

    
    /**
     * Run the database seeds.
     */
    public function run(OpenDataQueries $queries, Violation $violation, Building $building, ScheduleRun $schedule_run): void
    {

        /**
         * store schedule data in table
         */

        $schedule_run->name = 'update_lead_violations';
        $schedule_run->success = false;

        $schedule_run->save();

        /**
         * get the row count
        */

        $count = $data = Http::withQueryParameters([
            '$$app_token' => $queries->getAPIKey(),
            '$query' => $queries->getViolationsCountQuery()
        ])
        ->retry(5, 300, fn($exception)=>$exception instanceof ConnectionException)
        ->get($queries->getEndpoint());
    
        if(!$count->ok()):

            Log::error($count->body());

            return;

        endif;

        $count_decoded = json_decode($count->body());

        $this->count = 3000;
        

        /**
         * get the data
         */

        $data = Http::pool(function(Pool $pool)use($queries){
            
            $pool_array = [];

            for($offset = 0; $offset <= $this->count; $offset = $offset + $this->limit){
                
                array_push($pool_array, $pool->withQueryParameters([
                    '$$app_token' => $queries->getAPIKey(),
                    '$select' => $queries->getSelectedColumns(),
                    '$limit' => $this->limit,
                    '$offset' => $offset,
                    '$where' => "caseless_one_of(`ordernumber`,{$queries->getOrderNumbers()})",
                    '$order' => 'violationid'
                ])->get($queries->getEndpoint()));
            }

            return $pool_array;
        });
        
        /**
         * store the data
         */
        
        foreach($data as $d):

            if(!$d->ok()):
                
                Log::error($d);

                return;
            
            endif;

            $d_decoded = json_decode($d->body());
            

            foreach($d_decoded as $attributes):

                $current_building = $building->where('nyc_open_data_building_id', (int)$attributes->buildingid)->first();
                
                if(!$current_building):
                    
                    $building->create([
                        'nyc_open_data_building_id' => $attributes->buildingid,
                        'bin' => isset($attributes->bin) ? $attributes->bin : null,
                        'address' => "$attributes->housenumber $attributes->streetname",
                        'point' => isset($attributes->longitude, $attributes->latitude) ? new Point($attributes->latitude, $attributes->longitude, Srid::WGS84->value) : null,
                        'zip' => isset($attributes->zip) ? $attributes->zip : null,

                    ]);

                else:

                    if($current_building->bin === null && isset($attributes->bin)):
                        
                        $current_building->bin = $attributes->bin;
                        $current_building->save();

                    endif;

                    if($current_building->point === null && isset($attributes->longitude, $attributes->latitude)):
                        
                        $current_building->point = new Point($attributes->latitude, $attributes->longitude, Srid::WGS84->value);
                        $current_building->save();

                    endif;


                    if($current_building->zip === null && isset($attributes->zip)):
                        
                        $current_building->zip = $attributes->zip;
                        $current_building->save();

                    endif;

                endif;
                
                $violation->create([
                    'nyc_open_data_violation_id' => $attributes->violationid,
                    'building_id' => $attributes->buildingid,
                    'ordernumber' => $attributes->ordernumber,
                    'inspectiondate' => $attributes->inspectiondate,
                    'currentstatusdate' => $attributes->currentstatusdate,
                    'currentstatusid' => $attributes->currentstatusid,
                    'apartment' => isset($attributes->apartment) ? $attributes->apartment : null
                ]);

            endforeach;
            
        endforeach;

        $schedule_run->success = true;
        $schedule_run->completed_on = Carbon::now();
        $schedule_run->save();
    }
}
