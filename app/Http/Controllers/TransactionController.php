<?php

namespace App\Http\Controllers;

use App\Services\TransactionSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionSearchService $transactionService
    ) {}

    /**
     * Affiche l'interface de recherche de transactions.
     */
    public function index(Request $request): View
    {
        $externalId = $request->query('external_id');
        $internalId = $request->query('internal_id');
        $queryId = $request->query('id', $externalId ?: $internalId);
        $type = $request->query('type', $internalId ? 'internal_id' : 'external_id');

        $initialResult = null;
        if (!empty($queryId)) {
            if ($type === 'internal_id') {
                $initialResult = $this->transactionService->searchByInternalId($queryId);
            } elseif ($type === 'external_id') {
                $initialResult = $this->transactionService->searchByExternalId($queryId);
            } else {
                $initialResult = $this->transactionService->searchAuto($queryId);
            }
        }

        return view('support.transactions.index', [
            'initialQuery' => $queryId,
            'initialType' => $type,
            'initialResult' => $initialResult,
        ]);
    }

    /**
     * Endpoint API / AJAX pour la recherche de transaction.
     */
    public function search(Request $request): JsonResponse
    {
        $id = $request->input('id') ?: $request->input('query_id') ?: $request->input('external_id') ?: $request->input('internal_id');
        $type = $request->input('type', 'auto');

        if (empty($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez renseigner un identifiant.',
                'count' => 0,
                'transactions' => [],
            ], 422);
        }

        $result = match ($type) {
            'external_id' => $this->transactionService->searchByExternalId((string) $id),
            'internal_id' => $this->transactionService->searchByInternalId((string) $id),
            default => $this->transactionService->searchAuto((string) $id),
        };

        return response()->json($result);
    }

    /**
     * Répond précisément à : GET /searchTrByInternalId/:internal_id
     */
    public function searchByInternalIdEndpoint(string $internal_id, Request $request): JsonResponse|View
    {
        $result = $this->transactionService->searchByInternalId($internal_id);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($result);
        }

        return view('support.transactions.index', [
            'initialQuery' => $internal_id,
            'initialType' => 'internal_id',
            'initialResult' => $result,
        ]);
    }

    /**
     * Recherche par external_id (support direct via GET /searchTrByExternalId/:external_id ou POST)
     */
    public function searchByExternalIdEndpoint(Request $request, ?string $external_id = null): JsonResponse|View
    {
        $id = $external_id ?: $request->input('external_id') ?: $request->query('external_id');

        if (empty($id)) {
            return response()->json([
                'success' => false,
                'message' => 'L\'identifiant externe (external_id) est requis.',
                'count' => 0,
                'transactions' => [],
            ], 422);
        }

        $result = $this->transactionService->searchByExternalId((string) $id);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($result);
        }

        return view('support.transactions.index', [
            'initialQuery' => (string) $id,
            'initialType' => 'external_id',
            'initialResult' => $result,
        ]);
    }
}
