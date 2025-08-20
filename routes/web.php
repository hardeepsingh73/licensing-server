<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ApiLogController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ErrorLogController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\LoginHistoryController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShadowLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
    // Permissions Routes
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::match(['get', 'post'], '/', [PermissionController::class, 'index'])->name('index');
        Route::get('/create', [PermissionController::class, 'create'])->name('create');
        Route::post('/store', [PermissionController::class, 'store'])->name('store');
        Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('edit');
        Route::put('/{permission}', [PermissionController::class, 'update'])->name('update');
        Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy');
    });
    // Roles Routes
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::match(['get', 'post'], '/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/store', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });
    // User Routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::match(['get', 'post'], '/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('store', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('settings')->name('settings.')->group(function () {
        // Route::match(['get', 'post'], '/', [SettingController::class, 'index'])->name('index');
        Route::get('/create', [SettingController::class, 'create'])->name('create');
        Route::post('/store', [SettingController::class, 'store'])->name('store');
        Route::get('/{setting}/edit', [SettingController::class, 'edit'])->name('edit');
        Route::put('/{setting}', [SettingController::class, 'update'])->name('update');
        Route::delete('/{setting}', [SettingController::class, 'destroy'])->name('destroy');
        Route::get('/', [SettingController::class, 'bulkedit'])->name('index');
        Route::post('/bulk', [SettingController::class, 'bulkupdate'])->name('bulk-update');
    });
    Route::prefix('login-history')->name('login-history.')->group(function () {
        Route::match(['get', 'post'], '/', [LoginHistoryController::class, 'index'])->name('index');
        Route::get('/my-login-history', [LoginHistoryController::class, 'myHistory'])->name('personal');
        Route::post('/clear', [LoginHistoryController::class, 'clear'])->name('clear');
    });

    Route::prefix('error-logs')->name('error-logs.')->group(function () {
        Route::match(['get', 'post'], '/', [ErrorLogController::class, 'index'])->name('index');
        Route::post('/clear', [ErrorLogController::class, 'clear'])->name('clear');
    });
    Route::prefix('email-logs')->name('email-logs.')->group(function () {
        Route::match(['get', 'post'], '/', [EmailLogController::class, 'index'])->name('index');
        Route::post('/clear', [emailLogController::class, 'clear'])->name('clear');
        Route::get('/{id}/show', [emailLogController::class,  'show'])->name('show');
    });

    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/{activityLog}', [ActivityLogController::class, 'show'])->name('show');
        Route::post('/clear', [ActivityLogController::class, 'clear'])->name('clear');
        Route::patch('/{id}/restore', [ActivityLogController::class, 'restore'])->name('restore');
    });
    Route::prefix('api-logs')->name('api-logs.')->group(function () {
        Route::match(['get', 'post'], '/', [ApiLogController::class, 'index'])->name('index');
        Route::get('/{apiLog}', [ApiLogController::class, 'show'])->name('show');
        Route::post('/clear', [ApiLogController::class, 'clear'])->name('clear');
    });

    Route::prefix('licenses')->name('licenses.')->group(function () {
        Route::match(['get', 'post'], '/', [LicenseController::class, 'index'])->name('index');
        Route::get('/create', [LicenseController::class, 'create'])->name('create');
        Route::post('/store', [LicenseController::class, 'store'])->name('store');
        Route::get('/{license}', [LicenseController::class, 'show'])->name('show');
        Route::get('/{license}/edit', [LicenseController::class, 'edit'])->name('edit');
        Route::put('/{license}', [LicenseController::class, 'update'])->name('update');
        Route::delete('/{license}', [LicenseController::class, 'destroy'])->name('destroy');
        Route::post('/{license}/reissue', [LicenseController::class, 'reissueKey'])->name('reissue');
        Route::post('/check-key', [LicenseController::class, 'checkKeyUniqueness'])->name('checkKey');
    });
});
Route::middleware(['auth', 'is_superadmin'])->group(function () {
    Route::get('/shadow-login/{user}', [ShadowLoginController::class, 'loginAsUser'])->name('shadow.login');
    Route::get('/shadow-logout', [ShadowLoginController::class, 'revertBack'])->name('shadow.logout');
});
require __DIR__ . '/auth.php';
