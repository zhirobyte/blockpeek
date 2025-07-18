<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackerController;
use App\Http\Controllers\BlockchainController;
use App\Http\Controllers\ChatController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Legacy API routes
Route::get('/eth-transactions', [TrackerController::class, 'getEthereumTransactions']);

// New enhanced API routes
Route::get('/dashboard', [BlockchainController::class, 'getDashboardData']);
Route::get('/search', [BlockchainController::class, 'searchTransactions']);
Route::get('/price-history', [BlockchainController::class, 'getPriceHistory']);
Route::post('/chat/process', [ChatController::class, 'processBlockchainQuery']);
