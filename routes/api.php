<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\ListingCreateController;
use App\Http\Controllers\Api\ListingDocumentController;
use App\Http\Controllers\Api\ListingIntakeController;
use App\Http\Controllers\Api\ListingWhatsAppQrController;
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
        Route::post('listings',                                [ListingCreateController::class, 'store']);
        Route::post('listings/intake',                         [ListingIntakeController::class, 'store']);
        Route::patch('listings/{uuid}',                        [ListingIntakeController::class, 'update']);
        Route::get('listings/search',                          [ListingController::class, 'search']);
        Route::get('listings/resolve',                         [ListingController::class, 'resolve']);
        Route::get('listings/slug/{qrSlug}',                   [ListingController::class, 'showBySlug']);
        Route::get('listings/{uuid}/documents',                [ListingDocumentController::class, 'index']);
        Route::post('listings/{uuid}/documents',               [ListingDocumentController::class, 'store']);
        Route::post('listings/{uuid}/profile-photo',           [ListingDocumentController::class, 'updateProfilePhoto']);
        Route::post('listings/{uuid}/whatsapp-qr',             [ListingWhatsAppQrController::class, 'generate']);
        Route::get('listings/{uuid}',                          [ListingController::class, 'show']);
    });
});
