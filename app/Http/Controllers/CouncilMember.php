<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CouncilMember extends Controller
{
    public function getCouncilMember($district_id){
        return ['district' => "council district {$district_id}" ];
    }
}
