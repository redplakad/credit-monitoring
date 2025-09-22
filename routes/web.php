<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

// Nominatif Kredit Page (Inertia)
Route::middleware(['auth', 'verified'])->get('nominatif-kredit', function () {
    return Inertia::render('NominatifKredit/Index');
})->name('nominatif-kredit.index');

// Master Data Page (Inertia)
Route::middleware(['auth', 'verified'])->get('master-data', function () {
    return Inertia::render('MasterData/Index');
})->name('master-data.index');

// Master Data Web API routes (uses web session authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('api/master-data', [\App\Http\Controllers\Api\MasterDataController::class, 'index']);
    Route::delete('api/master-data/{datadate}', [\App\Http\Controllers\Api\MasterDataController::class, 'destroy']);
    Route::post('api/master-data/import', [\App\Http\Controllers\Api\MasterDataController::class, 'import']);
});
