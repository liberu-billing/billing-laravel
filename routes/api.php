<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\KnowledgeBaseController;
use App\Http\Controllers\Api\CannedResponseController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\ClientContactController;
use App\Http\Controllers\Api\PackageGroupController;
use App\Http\Controllers\ClientNoteController;
use App\Http\Controllers\InstallationController;

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

    // Installation endpoint
    Route::post('/install', [InstallationController::class, 'install']);

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

    // Webhook endpoints
    Route::apiResource('webhooks', WebhookController::class);
    Route::post('webhooks/{webhook}/test', [WebhookController::class, 'test']);
    Route::get('webhooks/{webhook}/events', [WebhookController::class, 'events']);
    Route::get('webhook-event-types', [WebhookController::class, 'eventTypes']);
    Route::post('webhook-events/{event}/retry', [WebhookController::class, 'retryEvent']);

    // Canned Response endpoints
    Route::get('canned-responses', [CannedResponseController::class, 'index']);
    Route::get('canned-responses/search', [CannedResponseController::class, 'search']);
    Route::get('canned-responses/categories', [CannedResponseController::class, 'categories']);
    Route::get('canned-responses/most-used', [CannedResponseController::class, 'mostUsed']);
    Route::get('canned-responses/variables', [CannedResponseController::class, 'variables']);
    Route::get('canned-responses/{shortcode}', [CannedResponseController::class, 'show']);
    Route::post('canned-responses/{shortcode}/use', [CannedResponseController::class, 'use']);

    // Quote/Proposal endpoints (Blesta)
    Route::apiResource('quotes', QuoteController::class);
    Route::post('quotes/{quote}/send', [QuoteController::class, 'send']);
    Route::post('quotes/{quote}/accept', [QuoteController::class, 'accept']);
    Route::post('quotes/{quote}/decline', [QuoteController::class, 'decline']);
    Route::post('quotes/{quote}/convert', [QuoteController::class, 'convert']);
    Route::get('quotes-statistics', [QuoteController::class, 'statistics']);

    // Client Contact endpoints (Blesta)
    Route::get('customers/{customer}/contacts', [ClientContactController::class, 'index']);
    Route::post('customers/{customer}/contacts', [ClientContactController::class, 'store']);
    Route::get('customers/{customer}/contacts/{contact}', [ClientContactController::class, 'show']);
    Route::put('customers/{customer}/contacts/{contact}', [ClientContactController::class, 'update']);
    Route::delete('customers/{customer}/contacts/{contact}', [ClientContactController::class, 'destroy']);
    Route::post('customers/{customer}/contacts/{contact}/make-primary', [ClientContactController::class, 'makePrimary']);

    // Package Group endpoints (Blesta)
    Route::apiResource('package-groups', PackageGroupController::class);
    Route::post('package-groups/{packageGroup}/packages', [PackageGroupController::class, 'addPackage']);
    Route::delete('package-groups/{packageGroup}/packages/{plan}', [PackageGroupController::class, 'removePackage']);
    Route::post('package-groups/{packageGroup}/reorder', [PackageGroupController::class, 'reorder']);
});

// Public Knowledge Base endpoints (no auth required)
Route::prefix('knowledge-base')->group(function () {
    Route::get('categories', [KnowledgeBaseController::class, 'categories']);
    Route::get('search', [KnowledgeBaseController::class, 'search']);
    Route::get('featured', [KnowledgeBaseController::class, 'featured']);
    Route::get('popular', [KnowledgeBaseController::class, 'popular']);
    Route::get('articles/{slug}', [KnowledgeBaseController::class, 'show']);
    Route::post('articles/{slug}/helpful', [KnowledgeBaseController::class, 'markHelpful']);
    Route::post('articles/{slug}/not-helpful', [KnowledgeBaseController::class, 'markNotHelpful']);
    Route::get('categories/{categoryId}/articles', [KnowledgeBaseController::class, 'byCategory']);
});
