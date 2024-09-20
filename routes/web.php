<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Violations2D;
use App\Http\Controllers\Violations3D;
use App\Http\Controllers\Buildings;
use App\Http\Controllers\CouncilMember;
use App\Http\Controllers\Districts;
use App\Http\Controllers\Years;
use App\Http\Controllers\District;
use App\Http\Controllers\Representatives;

use function PHPSTORM_META\map;

Route::get('/', function () {
    return [
        'Message' => "Welcome to the Lead Map API",
        'Source' => "NYC Open Data",
        'API Root' => '/api/v1',
        'Routes' => [
            '/years' => 'Returns all years between 2004 and the current year',
            '/violations' => 'GeoJSON of',
            '/violations-3d' => 'GeoJSON of violations geocoded to their buildings',
            'building/{nyc_open_data_building_id}' => 'returns building violation data',
            'senate' => 'returns violations by ny state senate district',
            '/senate/{district_id}' => 'returns violation data for a specific senate district',
            '/assembly/{district_id}' => 'returns violation data for a specific assembly district',
            '/council/{district_id}' => 'returns violation data for a specific council district'
        ]
    ];
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

    Route::get('/reps/{district_type}', [Representatives::class, 'getReps']);

});

