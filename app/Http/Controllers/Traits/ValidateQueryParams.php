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

    
}