<?php

use App\Http\Controllers\Web\ApiLogController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\ClientSubscriptionController;
use App\Http\Controllers\Web\DoctorDocumentController;
use App\Http\Controllers\Web\ListingAuditLogController;
use App\Http\Controllers\Web\ListingController;
use App\Http\Controllers\Web\MasterCityController;
use App\Http\Controllers\Web\MasterCountryController;
use App\Http\Controllers\Web\MasterLocationController;
use App\Http\Controllers\Web\MasterQualificationController;
use App\Http\Controllers\Web\MasterServiceController;
use App\Http\Controllers\Web\SettingController;
use App\Http\Controllers\Web\SubscriptionPlanController;
use Illuminate\Support\Facades\Route;

// Root → redirect to login
Route::get('/', fn () => redirect()->route('login'));

// Guest-only routes
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // All authenticated roles — read-only
    Route::get('master-cities',         [MasterCityController::class,         'index'])->name('master-cities.index');
    Route::get('master-locations',      [MasterLocationController::class,      'index'])->name('master-locations.index');
    Route::get('master-qualifications', [MasterQualificationController::class, 'index'])->name('master-qualifications.index');
    Route::get('master-services',       [MasterServiceController::class,       'index'])->name('master-services.index');

    // Listings — read routes (all auth roles)
    Route::get('listings-cities/{countryCode}',   [ListingController::class, 'getCities'])->name('listings.cities');
    Route::get('listings-locations/{cityId}',     [ListingController::class, 'getLocations'])->name('listings.locations');
    Route::get('listings',                        [ListingController::class, 'index'])->name('listings.index');
    Route::get('listings/{listing}',              [ListingController::class, 'show'])->whereNumber('listing')->name('listings.show');

    // Doctor documents — view only for all auth; admin+ for write (controller enforces)
    Route::get('listings/{listing}/documents',        [DoctorDocumentController::class, 'index'])->name('doctor-documents.index');
    Route::get('documents/{doctorDocument}',          [DoctorDocumentController::class, 'show'])->name('doctor-documents.show');
    Route::get('documents/{doctorDocument}/download', [DoctorDocumentController::class, 'download'])->name('doctor-documents.download');

    // Listing audit logs — admin+ only (controller enforces)
    Route::get('listing-audit-logs',              [ListingAuditLogController::class, 'index'])->name('listing-audit-logs.index');
    Route::get('listing-audit-logs/{listingAuditLog}', [ListingAuditLogController::class, 'show'])->name('listing-audit-logs.show');

    // Subscription plans — all auth can view
    Route::get('subscription-plans',              [SubscriptionPlanController::class, 'index'])->name('subscription-plans.index');
    Route::get('subscription-plans/{subscriptionPlan}', [SubscriptionPlanController::class, 'show'])->whereNumber('subscriptionPlan')->name('subscription-plans.show');

    // Client subscriptions — admin+ only (controller enforces abort 403 for user role)
    Route::get('client-subscriptions',            [ClientSubscriptionController::class, 'index'])->name('client-subscriptions.index');
    Route::get('client-subscriptions/{clientSubscription}', [ClientSubscriptionController::class, 'show'])->whereNumber('clientSubscription')->name('client-subscriptions.show');

    // Settings — admin+ only (controller enforces abort 403)
    Route::get('settings',                        [SettingController::class, 'index'])->name('settings.index');
    Route::get('settings/{setting}',              [SettingController::class, 'show'])->whereNumber('setting')->name('settings.show');

    // API logs & clients — all auth can view
    Route::get('api-logs',                        [ApiLogController::class, 'index'])->name('api-logs.index');
    Route::get('api-logs/{apiLog}',               [ApiLogController::class, 'show'])->name('api-logs.show');
    Route::get('clients',                         [ClientController::class, 'index'])->name('clients.index');
    Route::get('clients/{client}',                [ClientController::class, 'show'])->whereNumber('client')->name('clients.show');

    // Admin+ write routes for doctor documents
    Route::get('listings/{listing}/documents/create',      [DoctorDocumentController::class, 'create'])->name('doctor-documents.create');
    Route::post('listings/{listing}/documents',            [DoctorDocumentController::class, 'store'])->name('doctor-documents.store');
    Route::get('documents/{doctorDocument}/verify',        [DoctorDocumentController::class, 'verify'])->name('doctor-documents.verify');
    Route::patch('documents/{doctorDocument}/status',      [DoctorDocumentController::class, 'updateStatus'])->name('doctor-documents.update-status');
    Route::delete('documents/{doctorDocument}',            [DoctorDocumentController::class, 'destroy'])->name('doctor-documents.destroy');

    // QR generation — admin+
    Route::post('listings/{listing}/generate-qr',          [ListingController::class, 'generateQr'])->name('listings.generate-qr');

    // Super admin only
    Route::middleware('super_admin')->group(function () {
        Route::resource('clients', ClientController::class)->except(['index', 'show']);
        Route::post('clients/{client}/regenerate-keys', [ClientController::class, 'regenerateKeys'])
             ->name('clients.regenerate-keys');

        Route::resource('master-countries',      MasterCountryController::class     )->except('show');
        Route::resource('master-cities',         MasterCityController::class        )->except(['index', 'show']);
        Route::resource('master-locations',      MasterLocationController::class    )->except(['index', 'show']);
        Route::resource('master-qualifications', MasterQualificationController::class)->except(['index', 'show']);
        Route::resource('master-services',       MasterServiceController::class     )->except(['index', 'show']);
        Route::resource('listings',              ListingController::class           )->except(['index', 'show']);

        Route::resource('subscription-plans',    SubscriptionPlanController::class  )->except(['index', 'show']);
        Route::resource('client-subscriptions',  ClientSubscriptionController::class)->except(['index', 'show']);
        Route::resource('settings',              SettingController::class           )->except(['index', 'show']);
    });
});
