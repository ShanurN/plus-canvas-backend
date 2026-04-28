<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('auth')->group(function () {

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1'); // Limit login attempts

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Banner Routes
Route::get('/banners', [\App\Http\Controllers\Api\BannerController::class, 'index']);
Route::get('/banners/{banner}', [\App\Http\Controllers\Api\BannerController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/banners', [\App\Http\Controllers\Api\BannerController::class, 'store']);
    Route::post('/banners/reorder', [\App\Http\Controllers\Api\BannerController::class, 'reorder']);
    Route::put('/banners/{banner}', [\App\Http\Controllers\Api\BannerController::class, 'update']);
    Route::delete('/banners/{banner}', [\App\Http\Controllers\Api\BannerController::class, 'destroy']);
});
