<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VillaController;
use App\Http\Controllers\Api\WebsiteVillaController;
use App\Http\Controllers\Auth\ForgotPasswordController;

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
Route::post('/ical/sync', [WebsiteVillaController::class, 'syncIcal']);

Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

// Website API Key Protected Routes
Route::middleware('website.api')->prefix('website')->group(function () {
    Route::get('/villas', [WebsiteVillaController::class, 'index']);
    Route::get('/villas/{id}', [WebsiteVillaController::class, 'show']);
});

// --- Protected Routes (Require Sanctum Authentication) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/villas', [VillaController::class, 'index']);
    Route::get('/villas/{id}', [VillaController::class, 'show']);

    Route::get('/occupancy', [VillaController::class, 'getOccupancy']);
});