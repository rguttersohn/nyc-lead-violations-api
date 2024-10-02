<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Buildings2D;
use App\Http\Controllers\Buildings3D;
use App\Http\Controllers\Building;
use App\Http\Controllers\CouncilMember;
use App\Http\Controllers\Districts;
use App\Http\Controllers\Years;
use App\Http\Controllers\District;
use App\Http\Controllers\Representatives;
use App\Http\Controllers\City;
use App\Http\Controllers\Timelines;


Route::get('/', function () {
    return [
        'Message' => "Welcome to the Lead Map API",
        'Violations Source' => "NYC Open Data",
        "Geocoding Source" => "NYC GeoClient API"
    ];
});

Route::prefix('api/v1')->group(function(){

    Route::get('/years', [Years::class, 'getYears']);
 
    Route::get('/buildings', [Buildings2D::class, 'getBuildings']);

    Route::get('/buildings-3d', [Buildings3D::class, 'getBuildings']);

    Route::get('/buildings/{id}', [Building::class, 'getBuilding']);

    Route::get('districts', [Districts::class, 'getAllDistricts']);

    Route::get('districts/{district_type}', [Districts::class, 'getDistrictData']);

    Route::get('districts/{district_type}/{district_id}', [District::class, 'getDistrictDataWithID']);

    Route::get('city', [City::class, 'getCityData']);

    Route::get('/council-member/{district_id}',[CouncilMember::class, 'getCouncilMember'] );

    Route::get('/reps/{district_type}', [Representatives::class, 'getReps']);

    Route::get('timelines/districts/{district_type}/{district_id}', [Timelines::class, 'getDistrictTimeline']);

    Route::get('timelines/buildings/{id}', [Timelines::class, 'getBuildingTimeline']);

});

