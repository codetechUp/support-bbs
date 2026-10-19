<?php

namespace App\Http\Controllers;

use App\Services\IntegratedClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(
        protected IntegratedClientService $clientService
    ) {}

    /**
     * Affiche la liste des clients intégrés.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $clientsData = $this->clientService->getClients();

        return view('support.clients.index', compact('user', 'clientsData'));
    }

    /**
     * Retourne les clients sous forme de JSON pour le rafraîchissement dynamique (AJAX).
     */
    public function data(Request $request): JsonResponse
    {
        $forceRefresh = $request->boolean('refresh', false);
        $data = $this->clientService->getClients($forceRefresh);

        return response()->json($data);
    }

    /**
     * Endpoint API public / REST pour les clients intégrés.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $forceRefresh = $request->boolean('refresh', false);
        $data = $this->clientService->getClients($forceRefresh);

        return response()->json([
            'status' => 'success',
            'summary' => $data['summary'],
            'clients' => $data['clients'],
        ]);
    }
}
