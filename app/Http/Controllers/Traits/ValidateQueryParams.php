<?php
namespace App\Http\Controllers\Traits;

trait ValidateQueryParams {


    private function getValidStatusQuery(string $status):string{

       $valid_status = match($status){

            'all' => 'all',
            'open' => 'open',
            'close'=> 'close',
            default => 'all'
    
       };
         
        return $valid_status;

    }

    private function statusNeedsToBeChecked($status):bool{

        $needs_to_checked = match($status){
            'open' => true, 
            'close' => true, 
            default => false
        };

        return $needs_to_checked;
    }

    private function getFormattedEndYear(string $end_year):string{

        return "$end_year-12-31";
    }

    private function getFormattedStartYear(string $start_year):string{

        return "$start_year-01-01";
    }
    
}