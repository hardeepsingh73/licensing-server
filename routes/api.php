<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

// User routes with user JWT authentication
Route::middleware('auth:api')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::post('/logout', [AuthController::class, 'logout']);

    // License activate (this usually needs user auth to activate license)
    Route::prefix('license')->name('license.')->controller(LicenseController::class)->group(function () {
        Route::post('/create', 'createKey');
    });
});
// License activate (this usually needs user auth to activate license)
Route::prefix('license')->name('license.')->controller(LicenseController::class)->group(function () {
    Route::post('/activate', 'activateKey');
    Route::post('/validate', 'validateKey');
});
// License routes with license JWT authentication
Route::middleware('auth:api_license')->prefix('license')->controller(LicenseController::class)->group(function () {
    Route::post('/reissue', 'reissueKey');
    Route::post('/devices', 'listDevices');
});
