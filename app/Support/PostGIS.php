<?php

namespace App\Support;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class PostGIS {

    public static function createSpatialJoin(string $table_name, string $point, string $first_geometry, string $second_geometry, string $boolean = "true"):array {
        
        return [DB::raw("ST_within($point, CASE WHEN $table_name.geo_type = $first_geometry THEN $table_name.$first_geometry WHEN $table_name.geo_type = $second_geometry THEN $table_name.$second_geometry END)"), "=", DB::raw("$boolean")];
    }

}