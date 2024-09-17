<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Violations2D;
use App\Http\Controllers\Violations3D;
use App\Http\Controllers\Buildings;
use App\Http\Controllers\CouncilMember;
use App\Http\Controllers\Districts;
use App\Http\Controllers\Years;
use App\Http\Controllers\District;


Route::get('/', function () {
    return "Welcome to the lead map api!!!";
});

Route::prefix('api/v1')->group(function(){

    Route::get('/years', [Years::class, 'getYears']);
 
    Route::get('/violations', [Violations2D::class, 'getViolations']);

    Route::get('/violations-3d', [Violations3D::class, 'getViolations']);

    Route::get('/building/{nyc_open_data_building_id}', [Buildings::class, 'getBuilding']);

    Route::get('/senate', [Districts::class, 'getSenateData']);

    Route::get('/senate/{district_id}', [District::class, 'getSenateDataWithID']);

    Route::get('/assembly', [Districts::class, 'getAssemblyData']);

    Route::get('/assembly/{district_id}', [District::class, 'getAssemblyDataWithID']);

    Route::get('/council', [Districts::class, 'getCouncilData']);

    Route::get('/council/{district_id}', [District::class, 'getCouncilDataWithID']);

    Route::get('/council-member/{district_id}',[CouncilMember::class, 'getCouncilMember'] );

});

