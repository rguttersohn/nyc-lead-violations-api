<?php

namespace App\Support;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class PostGIS {

    public static function createSpatialJoin(string $point, string $area, string $boolean = "true"):array {
        
        return [DB::raw("st_within($point, $area)"), "=", DB::raw("$boolean")];
    }
}