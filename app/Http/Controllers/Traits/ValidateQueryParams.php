<?php
namespace App\Http\Controllers\Traits;

use App\Models\Code;

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

    private function codeNeedsFiltering($code):bool{

        $needs_filter = match($code){
            'all' => false, 
            default => true
        };

        return $needs_filter;
    }

    private function getValidCodeQuery($code):string{

        if($code === 'all'):
            
            return 'all';
        
        endif;

        if(!is_numeric($code)):
            return 'all';
        endif;

        $valid_code = Code::select('ordernumber')->currentCode($code)->first();

        if(!$valid_code):
            return 'all';
        endif;

        return $valid_code->ordernumber;

    }
    
}