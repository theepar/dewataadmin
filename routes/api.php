<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IcalLinkController;
use App\Http\Controllers\Api\VillaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// --- Public Routes ---
Route::post('/login', [AuthController::class, 'login']);

// --- Website API Routes ---
Route::prefix('web')->middleware('website.api')->group(function () {
    Route::get('/villas', [VillaController::class, 'index']);
    Route::get('/villas/{id}', [VillaController::class, 'show']);
});

// --- Protected Routes (Require Sanctum Authentication) ---
Route::middleware('auth:sanctum')->group(function () {
    // Get current authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Logout endpoint
    Route::post('/logout', [AuthController::class, 'logout']);

    // Villas endpoints
    Route::get('/villas', [VillaController::class, 'index']);
    Route::get('/villas/{id}', [VillaController::class, 'show']);

    // Ical Links endpoints
    Route::get('/ical-links', [IcalLinkController::class, 'index']);
    Route::get('/ical-links/{id}', [IcalLinkController::class, 'show']);
});