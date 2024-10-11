<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
        
        DB::statement('CREATE INDEX district_polygon_gix ON districts USING GIST (polygon);');
        DB::statement('CREATE INDEX district_multipolygon_gix ON districts USING GIST (multipolygon);');
        DB::statement('CREATE INDEX buildings_point_gix ON buildings USING GIST (point);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS district_polygon_gix;');
        DB::statement('DROP INDEX IF EXISTS district_multipolygon_gix;');
        DB::statement('DROP INDEX IF EXISTS buildings_point_gix');
        
    }
};
