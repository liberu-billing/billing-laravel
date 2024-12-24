<?php

use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketResponseController;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\ClientController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['auth'])->group(function () {
    Route::resource('tickets', TicketController::class);
    Route::post('tickets/{ticket}/responses', [TicketResponseController::class, 'store'])
        ->name('ticket.responses.store');
        
    // Client Service Management Routes
    Route::prefix('client')->name('client.')->group(function () {
        Route::get('/services', [ServiceManagementController::class, 'index'])->name('services.index');
        Route::get('/services/{subscription}', [ServiceManagementController::class, 'show'])->name('services.show');
        Route::post('/services/{subscription}/upgrade', [ServiceManagementController::class, 'upgrade'])->name('services.upgrade');
        Route::post('/services/{subscription}/downgrade', [ServiceManagementController::class, 'downgrade'])->name('services.downgrade');
        Route::post('/services/{subscription}/cancel', [ServiceManagementController::class, 'cancel'])->name('services.cancel');
    });

    // Advanced Search Routes
    Route::get('/api/search-suggestions', [ClientNoteController::class, 'suggestions']);
    Route::apiResource('/api/saved-searches', SavedSearchController::class);
    Route::post('/api/shared-searches', [SavedSearchController::class, 'share']);
    Route::get('/api/shared-searches/{token}', [SavedSearchController::class, 'loadShared']);
});

Route::get('/', fn () => view('welcome'));

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::resource('clients', ClientController::class);
});

// Route::redirect('/login', '/app/login')->name('login');

// Route::redirect('/register', '/app/register')->name('register');

Route::redirect('/dashboard', '/app')->name('dashboard');

Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'accept'])
    ->middleware(['signed', 'verified', 'auth', AuthenticateSession::class])
    ->name('team-invitations.accept');

require __DIR__.'/socialstream.php';


<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', '2fa'])->prefix('admin')->group(function () {
    // Admin routes go here
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/reminder-settings', [ReminderSettingsController::class, 'edit'])
        ->name('reminder-settings.edit');
    Route::put('/reminder-settings', [ReminderSettingsController::class, 'update'])
        ->name('reminder-settings.update');
});