<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('nominatif-kredit', [\App\Http\Controllers\Api\NominatifKreditController::class, 'index']);
    Route::get('nominatif-kredit/{id}', [\App\Http\Controllers\Api\NominatifKreditController::class, 'show']);
    Route::get('nominatif-kredit-filter-options', [\App\Http\Controllers\Api\NominatifKreditController::class, 'getFilterOptions']);

    // Master Data endpoints
    Route::get('master-data', [\App\Http\Controllers\Api\MasterDataController::class, 'index']);
    Route::delete('master-data/{datadate}', [\App\Http\Controllers\Api\MasterDataController::class, 'destroy']);
});
