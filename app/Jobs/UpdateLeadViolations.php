<?php

namespace App\Jobs;

use App\Models\ScheduleRuns;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\OpenDataQueries;
use App\Models\Violation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use App\Models\ScheduleRun;
use Illuminate\Support\Str;
use App\Models\Building;


class UpdateLeadViolations implements ShouldQueue
{
    use Queueable;

    public $violation;

    public $schedule_run;
    public $building;
    /**
     * Create a new job instance.
     */
    public function __construct(Violation $violation, Building $building, ScheduleRun $schedule_run)
    {
        $this->violation = $violation;
        $this->building = $building;
        $this->schedule_run = $schedule_run;
    }

    /**
     * Execute the job.
     */
    public function handle(OpenDataQueries $queries): void
    {
        $last_schedule = ScheduleRun::whereNotNull('completed_on')->where([['name','update_lead_violations'],['success', true]])->latest()->first();
        
        $last_completed_on = '';

        if(!$last_schedule):

            $last_completed_on = Carbon::now()->format('Y-m-d H-i-s');

        else:

            $last_completed_on = $last_schedule['completed_on'];

        endif;

        $timestamp = Str::replaceFirst(' ', 'T', $last_completed_on);

        $timestamp = "2024-09-01T00:00:00";
        $this->schedule_run->name = 'update_lead_violations';
        $this->schedule_run->success = false;

        $this->schedule_run->save();

        $data = Http::withQueryParameters([
            '$$app_token' => $queries->getAPIKey(),
            '$select' => $queries->getSelectedColumns(),
            '$where' => "caseless_one_of(`ordernumber`,{$queries->getOrderNumbers()}) AND {$queries->getDates($timestamp)}",
            '$order' => 'currentstatusdate ASC',
            '$limit' => 5
        ])
        ->get($queries->getEndpoint());

        if(!$data->ok()):

            Log::error($data->body());

            $this->schedule_run->completed_on = Carbon::now();
            $this->schedule_run->save();
            die();

        endif;

        foreach(json_decode($data->body()) as $attributes):

            $current_building = $this->building->where('nyc_open_data_building_id', (int)$attributes->buildingid)->first();
            
            if(!$current_building):
                
                $this->building->create([
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
            
            $this->violation->updateOrCreate(
                ['nyc_open_data_violation_id' => $attributes->violationid],
                [
                    'building_id' => $attributes->buildingid,
                    'ordernumber' => $attributes->ordernumber,
                    'inspectiondate' => $attributes->inspectiondate,
                    'currentstatusdate' => $attributes->currentstatusdate,
                    'currentstatusid' => $attributes->currentstatusid,
                    'apartment' => isset($attributes->apartment) ? $attributes->apartment : null
                ]
            );

        endforeach;

        $this->schedule_run->success = true;
        $this->schedule_run->completed_on = Carbon::now();
        $this->schedule_run->save();

    }

}
