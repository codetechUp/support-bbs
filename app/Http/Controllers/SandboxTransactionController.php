<?php

namespace App\Http\Controllers;

use App\Services\ManyPayCashSandboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class SandboxTransactionController extends Controller
{
    /**
     * Afficher la page de consultation des transactions Sandbox.
     */
    public function index(): View
    {
        return view('support.sandbox.transactions.index');
    }

    /**
     * Récupérer les transactions Sandbox depuis ManyPayCash.
     */
    public function data(
        Request $request,
        ManyPayCashSandboxService $sandboxService
    ): JsonResponse {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'id_customer' => ['nullable', 'integer', 'min:1'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'debut' => ['nullable', 'string', 'max:100'],
            'fin' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $result = $sandboxService->listeTransactions([
                'page' => (int) ($validated['page'] ?? 1),
                'limit' => (int) ($validated['limit'] ?? 10),
                'id_customer' => $validated['id_customer'] ?? null,
                'external_id' => $validated['external_id'] ?? null,
                'debut' => $validated['debut'] ?? null,
                'fin' => $validated['fin'] ?? null,
            ]);

            return response()->json($result);

        } catch (Throwable $e) {
            Log::error(
                'Erreur lors de la récupération des transactions Sandbox.',
                [
                    'filters' => $validated,
                    'message' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Impossible de récupérer les transactions Sandbox pour le moment.',
            ], 502);
        }
    }
}
