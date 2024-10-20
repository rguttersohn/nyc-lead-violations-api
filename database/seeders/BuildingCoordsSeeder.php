<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Building;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Str;

class BuildingCoordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Building $building): void
    {

        $buildings = $building->select('id','bin', 'housenumber','streetname', 'boro')->get();


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
                
    }
}
