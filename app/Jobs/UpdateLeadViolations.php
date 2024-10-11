<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\OpenDataQueries;
use App\Models\Violation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\ScheduleRun;
use App\Models\Building;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;


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

        $timestamp = "{$last_completed_on}T00:00:00";

        $this->schedule_run->name = 'update_lead_violations';
        $this->schedule_run->success = false;

        $this->schedule_run->save();
        
        $data = Http::withQueryParameters([
            '$$app_token' => $queries->getAPIKey(),
            '$select' => $queries->getSelectedColumns(),
            '$where' => "caseless_one_of(`ordernumber`,{$queries->getOrderNumbers()}) AND {$queries->getDates($timestamp)}",
            '$order' => 'currentstatusdate ASC',
        ])
        ->get($queries->getEndpoint());

        if(!$data->ok()):

            Log::error($data->body());

            $this->schedule_run->completed_on = Carbon::now();
            $this->schedule_run->save();
            die();

        endif;

        if(!json_decode($data->body())):

            Log::error($data->body());

            $this->schedule_run->completed_on = Carbon::now();
            $this->schedule_run->save();
            die();

        endif;
        


        $new_buildings = [];

        foreach(json_decode($data->body()) as $attributes):
            if(!$attributes):
                die();
            endif;

            $current_building = $this->building->where('nyc_open_data_building_id', (int)$attributes->buildingid)->first();
            
            if(!$current_building):
                
                $this->building->create([
                    'nyc_open_data_building_id' => $attributes->buildingid,
                    'bin' => isset($attributes->bin) ? $attributes->bin : null,
                    'housenumber' => $attributes->housenumber,
                    'streetname' => $attributes->streetname,
                    'boro' => $attributes->boro
                ]);

                array_push($new_buildings, $attributes->buildingid);

            else:

                if($current_building->bin === null && isset($attributes->bin)):
                    
                    $current_building->bin = $attributes->bin;
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

        // geocode

        $buildings = $this->building->select('id','bin', 'housenumber','streetname', 'boro')->whereIn('nyc_open_data_building_id', $new_buildings)->get();

        $buildings->each(function($building){

            if($building->point):
                return;
            endif;

            dump("$building->housenumber $building->streetname, $building->boro");

            if($building->bin):
                
                $endpoint = 'bin';

                $geo_coding = Http::retry(5, function(int $attempt, Exception $exception){
                    if($exception instanceof RequestException):
                        
                        $message = $exception->getMessage();
                        $time_string = Str::between($message, 'Rate limit is exceeded. Try again in ', ' seconds.');
                        $time_string .= "000";
                        $attempt = (int) $time_string;
                        dump($attempt);
                        
                        return $attempt;        
                    
                    endif;

                })->get('https://api.nyc.gov/geo/geoclient/v2/bin.json',[
                    'bin' => $building->bin,
                    'key' => $_ENV['GEO_CODING_KEY']
                ]);
            
            else:

                $endpoint = 'address';

                $geo_coding = Http::retry(5, function(int $attempt, Exception $exception){
                    
                    if($exception instanceof RequestException):
                        
                        $message = $exception->getMessage();
                        $time_string = Str::between($message, 'Rate limit is exceeded. Try again in ', ' seconds.');
                        $time_string .= "000";

                        $attempt = (int) $time_string;
                        dump($attempt);
                        
                        return $attempt;
    
                    endif;

                })->get('https://api.nyc.gov/geo/geoclient/v2/address.json',[
                    'houseNumber' => $building->housenumber,
                    'street' => $building->streetname,
                    'borough' => $building->boro,
                    'key' => $_ENV['GEO_CODING_KEY']
                ]);

            endif;
      
            if(!$geo_coding->ok()):
            
                dump($geo_coding->body());

                Log::error($geo_coding->body());
                
                return;
            
            endif;

            $geo_decoded = json_decode($geo_coding->body());
            
            $longitude = isset($geo_decoded->{$endpoint}->longitudeInternalLabel) ? $geo_decoded->{$endpoint}->longitudeInternalLabel : null;
            $latitude = isset($geo_decoded->{$endpoint}->latitudeInternalLabel) ? $geo_decoded->{$endpoint}->latitudeInternalLabel : null;

            if($longitude && $latitude):
                                
                $building->point = new Point($latitude, $longitude, srid::WGS84->value);

                if(!$building->bin && isset($geo_decoded->{$endpoint}->buildingIdentificationNumber)):
                    
                    $building->bin = $geo_decoded->{$endpoint}->buildingIdentificationNumber;

                endif;

                $building->save();
                
                dump('save complete');

            else:

                dump('long and lat is empty', $building->housenumber, $building->streetname);

            endif;
     
        
        });

        $this->schedule_run->completed_on = Carbon::now();
        $this->schedule_run->success = true;
        $this->schedule_run->save();
        
    }

}
