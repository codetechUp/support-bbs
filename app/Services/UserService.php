<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class UserService
{
    /**
     * Récupère les utilisateurs depuis ManyPayCash.
     */
    public function getUsers(int $page = 1, int $limit = 10): array
    {
        $apiKey = config('services.manypaycash.api_key');
        $url = config('services.manypaycash.users_url');

        if (empty($apiKey)) {
            throw new RuntimeException(
                'La clé API ManyPayCash est absente de la configuration.'
            );
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withHeaders([
                    'x-api-key' => $apiKey,
                ])
                ->timeout(30)
                ->post($url, [
                    'page' => $page,
                    'limit' => $limit,
                ]);

            $result = $response->json();

            if (!$response->successful()) {
                $message = data_get($result, 'message');

                if (is_array($message)) {
                    $message = json_encode($message);
                }

                throw new RuntimeException(
                    $message ?: 'Erreur lors de la récupération des utilisateurs.',
                    $response->status()
                );
            }

            if (
                !is_array($result) ||
                !isset($result['data']) ||
                !is_array($result['data']) ||
                !isset($result['meta'])
            ) {
                throw new RuntimeException(
                    'La structure de réponse de ManyPayCash est invalide.'
                );
            }

            return [
                'meta' => $result['meta'],
                'data' => $result['data'],
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new RuntimeException(
                'Impossible de contacter le serveur ManyPayCash.',
                0,
                $e
            );
        }
    }
}
