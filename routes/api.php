<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentApiController;
use App\Http\Controllers\BehaviorReportController;
use App\Http\Controllers\ViolationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Student API routes
Route::middleware(['auth'])->group(function () {
    Route::get('/students/{id}', [StudentApiController::class, 'show']);
    
    // Behavior Report routes
    Route::get('/behavior-reports/recent', [BehaviorReportController::class, 'getRecentReports']);
    Route::get('/behavior-reports/students/search', [BehaviorReportController::class, 'searchStudents']);
    Route::get('/behavior-reports/{id}', [BehaviorReportController::class, 'show']);
    Route::post('/behavior-reports', [BehaviorReportController::class, 'store']);
    
    // Violation routes
    Route::get('/violations/all', [ViolationController::class, 'index']);
});