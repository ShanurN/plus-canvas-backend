<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CanvasFormatController as AdminCanvasFormatController;
use App\Http\Controllers\Admin\CanvasSizeController as AdminCanvasSizeController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\MainCategoryController as AdminMainCategoryController;
use App\Http\Controllers\Admin\SubCategoryController as AdminSubCategoryController;
use App\Http\Controllers\Admin\DiscountController as AdminDiscountController;
use App\Http\Controllers\Api\BannerController as PublicBannerController;
use App\Http\Controllers\Api\BrandController as PublicBrandController;
use App\Http\Controllers\Api\CanvasFormatController as PublicCanvasFormatController;
use App\Http\Controllers\Api\CanvasSizeController as PublicCanvasSizeController;
use App\Http\Controllers\Api\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\MainCategoryController as PublicMainCategoryController;
use App\Http\Controllers\Api\SubCategoryController as PublicSubCategoryController;
use App\Http\Controllers\Api\DiscountController as PublicDiscountController;
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

    // Handle unauthenticated redirects
    Route::get('/login', function () {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    })->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Banner Routes
Route::get('/banners', [PublicBannerController::class, 'index']);
Route::get('/banners/{banner}', [PublicBannerController::class, 'show']);

// Category Routes
Route::get('/main-categories', [PublicMainCategoryController::class, 'index']);
Route::get('/categories', [PublicCategoryController::class, 'index']);
Route::get('/sub-categories', [PublicSubCategoryController::class, 'index']);

// Brand Routes
Route::get('/brands', [PublicBrandController::class, 'index']);

// Discount Routes
Route::get('/discounts', [PublicDiscountController::class, 'index']);
Route::get('/discounts/{id}', [PublicDiscountController::class, 'show']);

// Canvas Size Routes
Route::get('/canvas-sizes', [PublicCanvasSizeController::class, 'index']);

// Canvas Format Routes
Route::get('/canvas-formats', [PublicCanvasFormatController::class, 'index']);

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/banners', [AdminBannerController::class, 'index']);
    Route::get('/banners/{banner}', [AdminBannerController::class, 'show']);
    Route::post('/banners', [AdminBannerController::class, 'store']);
    Route::post('/banners/reorder', [AdminBannerController::class, 'reorder']);
    Route::post('/banners/{banner}', [AdminBannerController::class, 'update']); // Use POST for multipart
    Route::delete('/banners/{banner}', [AdminBannerController::class, 'destroy']);

    // Category Routes
    Route::get('/main-categories', [AdminMainCategoryController::class, 'index']);
    Route::post('/main-categories', [AdminMainCategoryController::class, 'store']);
    Route::get('/main-categories/{main_category}', [AdminMainCategoryController::class, 'show']);
    Route::post('/main-categories/reorder', [AdminMainCategoryController::class, 'reorder']);
    Route::post('/main-categories/{main_category}', [AdminMainCategoryController::class, 'update']);
    Route::delete('/main-categories/{main_category}', [AdminMainCategoryController::class, 'destroy']);

    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::get('/categories/{category}', [AdminCategoryController::class, 'show']);
    Route::post('/categories/reorder', [AdminCategoryController::class, 'reorder']);
    Route::post('/categories/{category}', [AdminCategoryController::class, 'update']); // for multipart
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);

    Route::get('/sub-categories', [AdminSubCategoryController::class, 'index']);
    Route::post('/sub-categories', [AdminSubCategoryController::class, 'store']);
    Route::get('/sub-categories/{sub_category}', [AdminSubCategoryController::class, 'show']);
    Route::post('/sub-categories/reorder', [AdminSubCategoryController::class, 'reorder']);
    Route::post('/sub-categories/{sub_category}', [AdminSubCategoryController::class, 'update']);
    Route::delete('/sub-categories/{sub_category}', [AdminSubCategoryController::class, 'destroy']);

    // Brand Routes
    Route::get('/brands', [AdminBrandController::class, 'index']);
    Route::post('/brands', [AdminBrandController::class, 'store']);
    Route::get('/brands/{brand}', [AdminBrandController::class, 'show']);
    Route::post('/brands/reorder', [AdminBrandController::class, 'reorder']);
    Route::put('/brands/{brand}', [AdminBrandController::class, 'update']);
    Route::delete('/brands/{brand}', [AdminBrandController::class, 'destroy']);

    // Discount Routes
    Route::get('/discounts', [AdminDiscountController::class, 'index']);
    Route::post('/discounts', [AdminDiscountController::class, 'store']);
    Route::get('/discounts/{discount}', [AdminDiscountController::class, 'show']);
    Route::post('/discounts/reorder', [AdminDiscountController::class, 'reorder']);
    Route::post('/discounts/{discount}', [AdminDiscountController::class, 'update']); // for multipart
    Route::delete('/discounts/{discount}', [AdminDiscountController::class, 'destroy']);

    // Canvas Size Routes
    Route::get('/canvas-sizes', [AdminCanvasSizeController::class, 'index']);
    Route::post('/canvas-sizes', [AdminCanvasSizeController::class, 'store']);
    Route::get('/canvas-sizes/{canvasSize}', [AdminCanvasSizeController::class, 'show']);
    Route::post('/canvas-sizes/reorder', [AdminCanvasSizeController::class, 'reorder']);
    Route::put('/canvas-sizes/{canvasSize}', [AdminCanvasSizeController::class, 'update']);
    Route::delete('/canvas-sizes/{canvasSize}', [AdminCanvasSizeController::class, 'destroy']);

    // Canvas Format Routes
    Route::get('/canvas-formats', [AdminCanvasFormatController::class, 'index']);
    Route::post('/canvas-formats', [AdminCanvasFormatController::class, 'store']);
    Route::get('/canvas-formats/{canvasFormat}', [AdminCanvasFormatController::class, 'show']);
    Route::post('/canvas-formats/reorder', [AdminCanvasFormatController::class, 'reorder']);
    // Update can be PUT since it doesn't have files
    Route::put('/canvas-formats/{canvasFormat}', [AdminCanvasFormatController::class, 'update']);
    Route::delete('/canvas-formats/{canvasFormat}', [AdminCanvasFormatController::class, 'destroy']);
});
