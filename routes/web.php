<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackerController;
use App\Http\Controllers\BlockchainController;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('blockpeek');
});

// Legacy route for backward compatibility
Route::get('/api/eth-transactions', [TrackerController::class, 'getEthereumTransactions']);

// New enhanced API routes
Route::get('/api/dashboard', [BlockchainController::class, 'getDashboardData']);
Route::get('/api/search', [BlockchainController::class, 'searchTransactions']);
Route::get('/api/price-history', [BlockchainController::class, 'getPriceHistory']);
Route::post('/api/chat/process', [ChatController::class, 'processBlockchainQuery']);
