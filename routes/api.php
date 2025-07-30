<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VillaController; // Impor VillaController
use App\Http\Controllers\Api\IcalLinkController; // Impor IcalLinkController
use App\Http\Controllers\Api\IcalEventController; // Impor IcalEventController

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

// --- Rute Publik (Tidak memerlukan autentikasi) ---
Route::post('/login', [AuthController::class, 'login']);
// Route::post('/register', [AuthController::class, 'register']); // Aktifkan jika Anda punya method register di AuthController

// --- Rute yang Memerlukan Autentikasi API (melalui Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    // Endpoint untuk mendapatkan informasi user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Endpoint untuk logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- Endpoint untuk Villas ---
    Route::get('/villas', [VillaController::class, 'index']);
    Route::get('/villas/{id}', [VillaController::class, 'show']);

    // --- Endpoint untuk Ical Links ---
    Route::get('/ical-links', [IcalLinkController::class, 'index']);
    Route::get('/ical-links/{id}', [IcalLinkController::class, 'show']);

    // --- Endpoint untuk Ical Events ---
    Route::get('/ical-events', [IcalEventController::class, 'index']);
    Route::get('/ical-events/{id}', [IcalEventController::class, 'show']);
});