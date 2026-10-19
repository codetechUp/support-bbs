<?php

namespace App\Http\Controllers;

use App\Services\PartnerServiceStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function __construct(
        protected PartnerServiceStatusService $statusService
    ) {}

    /**
     * Affiche l'espace agent support avec les données de disponibilité des services.
     */
    public function dashboard(Request $request): View
    {
        $user = Auth::user();
        $statusData = $this->statusService->getStatusData();

        return view('support.dashboard', compact('user', 'statusData'));
    }

    /**
     * Endpoint interne pour rafraîchir le statut des services en direct (AJAX).
     */
    public function servicesStatus(Request $request): JsonResponse
    {
        $forceRefresh = $request->boolean('refresh', false);
        $data = $this->statusService->getStatusData($forceRefresh);

        return response()->json($data);
    }
}
