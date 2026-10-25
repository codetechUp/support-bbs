<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SupportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Racine : redirection intelligente selon l'état et le rôle
Route::get('/', function () {
    if (Auth::check()) {
        /** @var User $user */
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole('support')) {
            return redirect()->route('support.dashboard');
        }
    }

    return redirect()->route('login');
})->name('home');

// Routes d'authentification
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Espace réservé à l'Administrateur
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/supports/create', [AdminController::class, 'createSupport'])->name('supports.create');
    Route::post('/supports', [AdminController::class, 'storeSupport'])->name('supports.store');
});

use App\Http\Controllers\ClientController;
use App\Http\Controllers\TransactionController;
use App\Models\User;

// Espace réservé à l'Agent Support (et accessible à l'Administrateur)
Route::middleware(['auth', 'role:support|admin'])->prefix('support')->name('support.')->group(function () {
    Route::get('/dashboard', [SupportController::class, 'dashboard'])->name('dashboard');
    Route::get('/services/status', [SupportController::class, 'servicesStatus'])->name('services.status');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/data', [ClientController::class, 'data'])->name('clients.data');

    // Recherche de transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions/search', [TransactionController::class, 'search'])->name('transactions.search');
    Route::post('/transactions/customer-search', [TransactionController::class, 'searchCustomerTransactions'])->name('transactions.customer-search');
});

// Endpoint direct demandé : GET /searchTrByInternalId/:internal_id
Route::get('/searchTrByInternalId/{internal_id}', [TransactionController::class, 'searchByInternalIdEndpoint'])
    ->name('search.internal');

// Endpoint direct : Recherche par external_id
Route::match(['get', 'post'], '/searchTrByExternalId/{external_id?}', [TransactionController::class, 'searchByExternalIdEndpoint'])
    ->name('search.external');
