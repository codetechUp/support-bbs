<?php

use App\Http\Controllers\Api\ServiceStatusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Endpoint permettant de retourner le statut opérationnel de chaque service :
| UP, DOWN, DEGRADED.
|
*/

Route::get('/services/status', [ServiceStatusController::class, 'index'])->name('api.services.status');
Route::get('/support/partenaire/status', [ServiceStatusController::class, 'index'])->name('api.support.partenaire.status');
Route::get('/support/services', [ServiceStatusController::class, 'index'])->name('api.support.services');
Route::get('/support/clients', [\App\Http\Controllers\ClientController::class, 'apiIndex'])->name('api.support.clients');

// Recherche de transactions API
Route::get('/searchTrByInternalId/{internal_id}', [\App\Http\Controllers\TransactionController::class, 'searchByInternalIdEndpoint'])->name('api.searchTrByInternalId');
Route::match(['get', 'post'], '/searchTrByExternalId/{external_id?}', [\App\Http\Controllers\TransactionController::class, 'searchByExternalIdEndpoint'])->name('api.searchTrByExternalId');
Route::post('/support/transactions/search', [\App\Http\Controllers\TransactionController::class, 'search'])->name('api.support.transactions.search');

