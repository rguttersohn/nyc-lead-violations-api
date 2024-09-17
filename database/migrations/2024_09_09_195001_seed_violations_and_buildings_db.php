<?php

use Illuminate\Database\Migrations\Migration;
use Database\Seeders\BuildingViolationSeeder;
use App\Services\OpenDataQueries;
use App\Models\Violation;
use App\Models\Building;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $seeder = new BuildingViolationSeeder();

        $queries = new OpenDataQueries();

        $violation = new Violation();
        
        $building = new Building();

        $seeder->run($queries, $violation, $building );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
