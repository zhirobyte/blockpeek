<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackerController;

Route::get('/', function () {
    return view('blockpeek');
});

Route::get('/api/eth-transactions', [TrackerController::class, 'getEthereumTransactions']);

Route::get('/', [TrackerController::class, 'getEthereumTransactions']);
