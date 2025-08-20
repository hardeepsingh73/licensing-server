<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::prefix('license')->name('license.')->controller(LicenseController::class)->group(function () {
        Route::post('/validate', 'validateKey')->name('validate');
        Route::post('/activate', 'activateKey')->name('activate');
        Route::post('/reissue', 'reissueKey')->name('reissue');
        Route::post('/devices', 'listDevices')->name('devices');
        Route::post('/create', 'createKey')->name('create');
    });
});
