<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Models\Code;

class Codes extends Controller
{
    public function getCodes():Collection{

        return Code::select('ordernumber', 'definition')->get(); 
    }
}
