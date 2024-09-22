<?php
use Illuminate\Support\Facades\Schedule;
use App\Jobs\UpdateLeadViolations;
use App\Models\Violation;
use App\Services\OpenDataQueries;
use App\Models\ScheduleRun;
use App\Models\Building;


Schedule::call(function(){


    $violation = new Violation();

    $building = new Building();

    $queries = new OpenDataQueries();

    $schedule_run = new ScheduleRun();


    $job = new UpdateLeadViolations($violation, $building, $schedule_run);

    $job->handle($queries);

})->timezone('America/New_York')->dailyAt('16:08');