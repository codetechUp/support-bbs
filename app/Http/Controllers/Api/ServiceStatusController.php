<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PartnerServiceStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceStatusController extends Controller
{
    public function __construct(
        protected PartnerServiceStatusService $statusService
    ) {}

    /**
     * Retourne le statut de chaque service (UP, DOWN, DEGRADED).
     */
    public function index(Request $request): JsonResponse
    {
        $forceRefresh = $request->boolean('refresh', false);
        $data = $this->statusService->getStatusData($forceRefresh);

        // Si le paramètre format=simple est demandé, retourner directement la liste simple
        if ($request->query('format') === 'flat') {
            return response()->json($data['services']);
        }

        return response()->json([
            'status' => 'success',
            'summary' => $data['summary'],
            'services' => $data['services'],
            'partners' => $data['partners'],
        ]);
    }
}
