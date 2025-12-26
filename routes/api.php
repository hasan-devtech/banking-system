<?php

use App\Http\Controllers\Api\AccountChangeController;
// use App\Http\Controllers\Api\Admin\AccountChangeController as ChangeRequestController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\Admin\ChangeRequestController as AdminChangeRequestController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\Admin\TransactionController as AdminTransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// --- Public Routes ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::post('/accounts', [AccountController::class, 'store']);
    Route::get('/accounts/{id}', [AccountController::class, 'show']);

    Route::post('/transactions/deposit', [TransactionController::class, 'deposit']);
    Route::post('/transactions/transfer', [TransactionController::class, 'transfer']);


    Route::post('/accounts/{id}/change-request', [AccountChangeController::class, 'store']);
    
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    
    // View Pending
    Route::get('/transactions/pending', [AdminTransactionController::class, 'index']);
    
    // Actions
    Route::post('/transactions/{id}/approve', [AdminTransactionController::class, 'approve']);
    Route::post('/transactions/{id}/reject', [AdminTransactionController::class, 'reject']);


    Route::get('/change-requests', [AdminChangeRequestController::class, 'index']);
    Route::post('/change-requests/{id}/approve', [AdminChangeRequestController::class, 'approve']);
    Route::post('/change-requests/{id}/reject', [AdminChangeRequestController::class, 'reject']);

});