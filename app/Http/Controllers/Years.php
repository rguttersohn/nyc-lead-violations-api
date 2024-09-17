<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;

class Years extends Controller
{
    public function getYears(){
        return range(Carbon::now()->year, 2004);
    }
}
