<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Affiche la page de gestion des utilisateurs.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $page = 1;
        $limit = 10;

        $usersData = [
            'meta' => [
                'total' => 0,
                'perPage' => $limit,
                'currentPage' => $page,
                'lastPage' => 1,
                'firstPage' => 1,
                'nextPageUrl' => null,
                'previousPageUrl' => null,
            ],
            'data' => [],
        ];

        $error = null;

        try {
            $usersData = $this->userService->getUsers($page, $limit);
        } catch (Throwable $e) {
            report($e);
            $error = 'Impossible de récupérer les utilisateurs auprès de ManyPayCash.';
        }

        return view('support.users.index', compact(
            'user',
            'usersData',
            'error'
        ));
    }

    /**
     * Retourne les utilisateurs en JSON pour le rafraîchissement dynamique.
     */
    public function data(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $page = (int) ($validated['page'] ?? 1);
        $limit = (int) ($validated['limit'] ?? 10);

        try {
            $usersData = $this->userService->getUsers($page, $limit);

            return response()->json([
                'status' => 'success',
                'meta' => $usersData['meta'],
                'users' => $usersData['data'],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de récupérer les utilisateurs.',
            ], 502);
        }
    }
}
