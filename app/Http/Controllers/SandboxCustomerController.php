<?php

namespace App\Http\Controllers;

use App\Services\ManyPayCashSandboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class SandboxCustomerController extends Controller
{
    /**
     * Afficher la liste des clients Sandbox.
     */
    public function index(): View
    {
        return view('support.sandbox.customers.index');
    }

    /**
     * Récupérer les clients Sandbox.
     */
    public function data(
        Request $request,
        ManyPayCashSandboxService $sandboxService
    ): JsonResponse {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        try {
            $result = $sandboxService->listeCustomers(
                (int) ($validated['page'] ?? 1),
                (int) ($validated['limit'] ?? 10)
            );

            return response()->json($result);
        } catch (Throwable $e) {
            Log::error('Erreur liste clients Sandbox ManyPayCash', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de récupérer les clients Sandbox.',
            ], 502);
        }
    }

    /**
     * Afficher le formulaire de création.
     */
    public function create(): View
    {
        return view('support.sandbox.customers.create');
    }

    /**
     * Créer un client Sandbox via ManyPayCash.
     */
    public function store(
        Request $request,
        ManyPayCashSandboxService $sandboxService
    ) {
        $validated = $request->validate([
            'entreprise' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ], [
            'entreprise.required' => 'Le nom de l’entreprise est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'nom.required' => 'Le nom est obligatoire.',
            'email.required' => 'L’adresse email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
        ]);

        try {
            $result = $sandboxService->createCustomer($validated);

            // L'API peut retourner une erreur dans son JSON,
            // même si la réponse HTTP est techniquement réussie.
            if (isset($result['code']) && (int) $result['code'] !== 200) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'api' => $result['message'] ?? 'La création du client a échoué.',
                    ]);
            }

            return redirect()
                ->route('support.sandbox.customers.index')
                ->with('success', $result['message'] ?? 'Client Sandbox créé avec succès.');
        } catch (Throwable $e) {
            Log::error('Erreur création client Sandbox ManyPayCash', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'api' => 'Impossible de créer le client Sandbox. Vérifiez les informations et réessayez.',
                ]);
        }
    }
}
