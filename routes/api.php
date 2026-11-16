<?php

use App\Http\Controllers\Api\AdminMonitorController;
use App\Http\Controllers\Api\PublicStatusController;
use App\Http\Middleware\EnsureIsAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// Endpoint Publik Tanpa Password (Rate limit 60 request/menit)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/public/status', [PublicStatusController::class, 'index']);
});

// Endpoint Cron HTTP Eksternal (Untuk trigger scheduler 1 menit via cron-job.org / UptimeRobot)
Route::middleware('throttle:10,1')->get('/cron/ping-all', function (Request $request) {
    $expectedKey = (string) env('ADMIN_PASSCODE', '');
    $providedKey = (string) $request->query('key', '');

    abort_unless($expectedKey !== '' && hash_equals($expectedKey, $providedKey), 401);

    Artisan::call('monitors:ping');

    return response()->json([
        'status' => 'ok',
        'ran_at' => now()->toIso8601String(),
    ]);
});

// Endpoint Admin Terproteksi Password + Anti Brute-Force (Maks 15 request/menit)
Route::middleware(['throttle:15,1', EnsureIsAdmin::class])
    ->prefix('admin')
    ->group(function () {
        Route::get('/monitors', [AdminMonitorController::class, 'index']);
        Route::post('/monitors', [AdminMonitorController::class, 'store']);
        Route::post('/monitors/ping-all', [AdminMonitorController::class, 'pingAll']);
        Route::post('/monitors/{monitor}/ping', [AdminMonitorController::class, 'ping']);
        Route::get('/monitors/{monitor}/logs', [AdminMonitorController::class, 'logs']);
        Route::delete('/monitors/{monitor}', [AdminMonitorController::class, 'destroy']);
    });