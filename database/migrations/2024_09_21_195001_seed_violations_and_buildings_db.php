<?php

use Illuminate\Database\Migrations\Migration;
use Database\Seeders\BuildingViolationSeeder;
use App\Services\OpenDataQueries;
use App\Models\Violation;
use App\Models\Building;
use App\Models\ScheduleRun;

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

        $schedule_run = new ScheduleRun();

        $seeder->run($queries, $violation, $building, $schedule_run );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
