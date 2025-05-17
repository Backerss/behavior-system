<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ViolationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes สำหรับจัดการประเภทพฤติกรรม
Route::prefix('violations')->group(function () {
    Route::get('/', [ViolationController::class, 'index']);
    Route::get('/all', [ViolationController::class, 'getAll']);
    Route::post('/', [ViolationController::class, 'store']);
    Route::get('/{id}', [ViolationController::class, 'show']);
    Route::put('/{id}', [ViolationController::class, 'update']);
    Route::delete('/{id}', [ViolationController::class, 'destroy']);
});