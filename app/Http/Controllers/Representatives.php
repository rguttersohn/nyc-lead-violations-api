<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Representatives extends Controller
{

    private function mapDistrictType(string $district_type):string | null{
        
        $valid_district_type = match($district_type){
            'senate' => 'state_upper',
            'assembly' => 'state_lower',
            'council' => 'local',
            default => false
        };

        return $valid_district_type;
    }


    public function getReps(Request $request, $district_type ){
        $latitude = $request->query('latitude', 40.730610);
        $longitude = $request->query('longitude', -73.935242);
        $mapped_district_type = $this->mapDistrictType($district_type);
        $key = $_ENV['CICERO_KEY'];

        if(!$latitude || !$longitude):
            return ['error' => 'Missing latitude and/or longitude parameters'];
        endif;

        if(!$mapped_district_type):
            return ['error' => 'Missing not a valid district type'];
        endif;

        $reps = Http::get('https://app.cicerodata.com/v3.1/official',[
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'json',
                'district_type' => $mapped_district_type,
                'max' => 200,
                'key' => $key
        ]);

        if(!$reps->ok()):
            return ['error' => $reps->body()];
        endif;

        $officials = json_decode($reps)->response->results->officials;
        
        return $response = [
            'first_name' => $officials[0]->first_name,
            'last_name' => $officials[0]->last_name,
            'email' => $officials[0]->email_addresses[0]
          ];;
            
      
    }
}
