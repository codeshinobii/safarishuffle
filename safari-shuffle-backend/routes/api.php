<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PackageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Package Management Routes
Route::prefix('packages')->group(function () {
    Route::get('/', [PackageController::class, 'index']);
    Route::get('/{package}', [PackageController::class, 'show']);
    
    // Apply throttle middleware to modification routes
    Route::post('/', [PackageController::class, 'store'])->middleware('throttle:api');
    Route::put('/{package}', [PackageController::class, 'update'])->middleware('throttle:api');
    Route::delete('/{package}', [PackageController::class, 'destroy'])->middleware('throttle:api');
    
    // Potentially apply to these too if needed, otherwise leave them
    Route::patch('/{package}/status', [PackageController::class, 'updateStatus']);
    Route::patch('/{package}/featured', [PackageController::class, 'toggleFeatured']);
    
    // Additional package management routes
    Route::get('/types', [PackageController::class, 'getTypes']);
    Route::get('/destinations', [PackageController::class, 'getDestinations']);
}); 