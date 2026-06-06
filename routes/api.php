<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\MasterDataController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // -----------------------------------------------------------------------
    // Public — no authentication required
    // -----------------------------------------------------------------------

    // Health check
    Route::get('/health', [HealthController::class, 'check']);

    // Admin login (System 1 — Sanctum token)
    Route::post('/login', [AuthController::class, 'login']);

    // -----------------------------------------------------------------------
    // HMAC-SHA256 authenticated — client-facing data APIs
    // -----------------------------------------------------------------------
    Route::middleware('hmac')->group(function () {
        // Master data
        Route::get('countries',                   [MasterDataController::class, 'countries']);
        Route::get('cities/{country_code}',       [MasterDataController::class, 'cities']);
        Route::get('locations/{city_id}',         [MasterDataController::class, 'locations']);
        Route::get('services',                    [MasterDataController::class, 'services']);
        Route::get('qualifications',              [MasterDataController::class, 'qualifications']);
        Route::get('settings/public',             [MasterDataController::class, 'publicSettings']);

        // Doctor listings
        Route::get('listings/search',             [ListingController::class, 'search']);
        Route::get('listings/slug/{qrSlug}',      [ListingController::class, 'showBySlug']);
        Route::get('listings/{uuid}',             [ListingController::class, 'show']);
    });
});
