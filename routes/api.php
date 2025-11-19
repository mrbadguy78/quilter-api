<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', RegisterController::class);

Route::middleware('auth:api')->group(function () {
    // Accounts
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::post('/accounts', [AccountController::class, 'store']);
    Route::get('/accounts/{account}', [AccountController::class, 'show']);

    // Transactions
    Route::get('/accounts/{account}/transactions', [TransactionController::class, 'index']);
    Route::post('/accounts/{account}/transactions', [TransactionController::class, 'store']);
});
