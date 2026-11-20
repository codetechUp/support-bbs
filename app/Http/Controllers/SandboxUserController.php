<?php

namespace App\Http\Controllers;

use App\Services\ManyPayCashSandboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class SandboxUserController extends Controller
{
    /**
     * Affiche la page de consultation des utilisateurs Sandbox.
     */
    public function index(): View
    {
        return view('support.sandbox.users.index');
    }

    /**
     * Récupère la liste des utilisateurs Sandbox depuis ManyPayCash.
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
            $result = $sandboxService->listeUsers(
                (int) ($validated['page'] ?? 1),
                (int) ($validated['limit'] ?? 100)
            );

            return response()->json($result);
        } catch (Throwable $e) {
            Log::error(
                'Erreur lors de la récupération des utilisateurs Sandbox : '
                .$e->getMessage()
            );

            return response()->json([
                'success' => false,
                'message' => 'Impossible de récupérer les utilisateurs Sandbox pour le moment.',
            ], 502);
        }
    }

    /**
     * Récupère les détails d'un utilisateur Sandbox par son adresse e-mail.
     */
    public function show(
        string $email,
        ManyPayCashSandboxService $sandboxService
    ): JsonResponse {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Adresse e-mail invalide.',
            ], 422);
        }

        try {
            $result = $sandboxService->getUserByEmail($email);

            return response()->json($result);
        } catch (Throwable $e) {
            Log::error(
                'Erreur lors de la récupération du détail utilisateur Sandbox.',
                [
                    'email' => $email,
                    'message' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Impossible de récupérer les informations de cet utilisateur.',
            ], 502);
        }
    }

    /**
     * Affiche les utilisateurs associés à un client Sandbox.
     */
    public function byCustomer(
        int $idCustomer,
        ManyPayCashSandboxService $sandboxService
    ): View {
        try {
            $users = $sandboxService->getUsersByCustomer($idCustomer);

            return view('support.sandbox.customers.users', [
                'idCustomer' => $idCustomer,
                'users' => $users,
                'error' => null,
            ]);
        } catch (Throwable $e) {
            Log::error(
                'Erreur lors de la récupération des utilisateurs associés au client Sandbox.',
                [
                    'id_customer' => $idCustomer,
                    'message' => $e->getMessage(),
                ]
            );

            return view('support.sandbox.customers.users', [
                'idCustomer' => $idCustomer,
                'users' => [],
                'error' => 'Impossible de récupérer les utilisateurs associés à ce client pour le moment.',
            ]);
        }
    }

    /**
     * Demande le renvoi des identifiants d'un utilisateur Sandbox.
     */
    public function sendCredentials(
        string $email,
        ManyPayCashSandboxService $sandboxService
    ): RedirectResponse {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'Adresse e-mail invalide.');
        }

        try {
            $result = $sandboxService->sendCredentials($email);

            Log::info(
                'Demande de renvoi des identifiants Sandbox transmise à ManyPayCash.',
                [
                    'email' => $email,
                    'api_response' => $result,
                ]
            );

            return back()->with(
                'success',
                'La demande de renvoi des identifiants a été traitée par ManyPayCash pour '.$email.'.'
            );
        } catch (Throwable $e) {
            Log::error(
                'Erreur lors du renvoi des identifiants Sandbox.',
                [
                    'email' => $email,
                    'message' => $e->getMessage(),
                ]
            );

            return back()->with(
                'error',
                'Impossible de traiter la demande de renvoi des identifiants pour le moment.'
            );
        }
    }
}
