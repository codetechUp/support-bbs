<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SandboxTransactionController;
use App\Http\Controllers\SandboxUserController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\UserController;
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
use App\Http\Controllers\SandboxCustomerController;
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
    Route::post('/transactions/verify-partner', [TransactionController::class, 'verifyPartner'])->name('transactions.verify-partner');

    // Gestion des utilisateurs
    Route::get('/users', [UserController::class, 'index'])
        ->name('users.index');

    Route::get('/users/data', [UserController::class, 'data'])
        ->name('users.data');

    Route::get(
        '/sandbox/customers/data',
        [SandboxCustomerController::class, 'data']
    )->name('sandbox.customers.data');

    Route::get(
        '/sandbox/customers',
        [SandboxCustomerController::class, 'index']
    )->name('sandbox.customers.index');

    Route::get(
        '/sandbox/customers/create',
        [SandboxCustomerController::class, 'create']
    )->name('sandbox.customers.create');

    Route::post(
        '/sandbox/customers',
        [SandboxCustomerController::class, 'store']
    )->name('sandbox.customers.store');

    // Consultation des utilisateurs Sandbox
    Route::get('/sandbox/users/data', [SandboxUserController::class, 'data'])
        ->name('sandbox.users.data');

    Route::get('/sandbox/users', [SandboxUserController::class, 'index'])
        ->name('sandbox.users.index');

    Route::get('/sandbox/users/{email}', [SandboxUserController::class, 'show'])
        ->where('email', '.*')
        ->name('sandbox.users.show');
    Route::post(
        '/sandbox/users/{email}/send-credentials',
        [SandboxUserController::class, 'sendCredentials']
    )
        ->where('email', '[^/]+')
        ->name('sandbox.users.send-credentials');

    // Consultation des utilisateurs associés à un client Sandbox
    Route::get(
        '/sandbox/customers/{idCustomer}/users',
        [SandboxUserController::class, 'byCustomer']
    )
        ->whereNumber('idCustomer')
        ->name('sandbox.customers.users');

    // Consultation des transactions Sandbox
    Route::get('/sandbox/transactions/data', [SandboxTransactionController::class, 'data'])
        ->name('sandbox.transactions.data');

    Route::get('/sandbox/transactions', [SandboxTransactionController::class, 'index'])
        ->name('sandbox.transactions.index');

});

// Endpoint direct demandé : GET /searchTrByInternalId/:internal_id
Route::get('/searchTrByInternalId/{internal_id}', [TransactionController::class, 'searchByInternalIdEndpoint'])
    ->name('search.internal');

// Endpoint direct : Recherche par external_id
Route::match(['get', 'post'], '/searchTrByExternalId/{external_id?}', [TransactionController::class, 'searchByExternalIdEndpoint'])
    ->name('search.external');

// Endpoint direct demandé : POST /searchTrByPartnerId
Route::post('/searchTrByPartnerId', [TransactionController::class, 'verifyPartner'])
    ->name('search.partner');
