<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\ClientNoteController;

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

// Public routes
Route::post('auth/token', [AuthController::class, 'token']);

// Protected routes
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // User endpoint
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Invoice endpoints
    Route::apiResource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download']);
    
    // Subscription endpoints
    Route::apiResource('subscriptions', SubscriptionController::class);
    Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew']);
    
    // Customer endpoints
    Route::apiResource('customers', CustomerController::class);
    Route::get('customers/{customer}/invoices', [CustomerController::class, 'invoices']);
    Route::get('customers/{customer}/subscriptions', [CustomerController::class, 'subscriptions']);

    // Client Notes endpoints
    Route::get('client-notes', [ClientNoteController::class, 'index']);
    Route::post('client-notes', [ClientNoteController::class, 'store']);
    Route::delete('client-notes/{note}', [ClientNoteController::class, 'destroy']);
});
