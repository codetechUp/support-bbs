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
        $type = $request->query(
            'type',
            $internalId ? 'internal_id' : 'external_id'
        );

        $initialResult = null;

        if (! empty($queryId)) {
            if ($type === 'internal_id') {
                $initialResult = $this->transactionService
                    ->searchByInternalId($queryId);
            } elseif ($type === 'external_id') {
                $initialResult = $this->transactionService
                    ->searchByExternalId($queryId);
            } else {
                $initialResult = $this->transactionService
                    ->searchAuto($queryId);
            }
        }

        $customers = [];
        $customersError = null;

        try {
            $customers = $this->transactionService->getCustomers();
        } catch (\Throwable $e) {
            report($e);

            $customersError =
                'Impossible de charger la liste des clients pour le moment.';
        }

        $initialCustomerId = $request->query('id_customer');

        return view('support.transactions.index', [
            'initialQuery' => $queryId,
            'initialType' => $type,
            'initialResult' => $initialResult,
            'initialCustomerId' => $initialCustomerId,
            'customers' => $customers,
            'customersError' => $customersError,
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

    /**
     * Recherche les transactions d'un client sur une période.
     */
    public function searchCustomerTransactions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_customer' => ['required', 'integer', 'min:1'],
            'debut' => ['required', 'date_format:Y-m-d'],
            'fin' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:debut',
            ],
        ]);

        $result = $this->transactionService->searchCustomerTransactions(
            (int) $validated['id_customer'],
            $validated['debut'],
            $validated['fin']
        );

        return response()->json($result);
    }

    /**
     * Vérifie une transaction directement chez le partenaire.
     *
     * POST /support/transactions/verify-partner ou POST /searchTrByPartnerId
     * Body: { "partner": "...", "id_partner": "..." }
     */
    public function verifyPartner(Request $request): JsonResponse
    {
        $partner = trim((string) ($request->input('partner') ?? ''));
        $idPartner = trim((string) (
            $request->input('id_partner')
            ?? $request->input('id_tr_partenaire')
            ?? $request->input('partner_transaction_id')
            ?? ''
        ));

        if ($partner === '' || $idPartner === '') {
            return response()->json([
                'success' => false,
                'message' => 'Les paramètres "partner" et "id_partner" sont obligatoires pour la vérification.',
            ], 422);
        }

        $result = $this->transactionService->searchByPartnerId($partner, $idPartner);

        return response()->json($result);
    }
}
