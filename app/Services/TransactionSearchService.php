<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransactionSearchService
{
    /**
     * Recherche une transaction par external_id (POST https://www.manypaycash.com/api/support/searchTrByExternalId)
     */
    public function searchByExternalId(string $externalId): array
    {
        $externalId = trim($externalId);
        if (empty($externalId)) {
            return [
                'success' => false,
                'query_type' => 'external_id',
                'query_id' => '',
                'count' => 0,
                'transactions' => [],
                'message' => 'L\'identifiant externe (external_id) est obligatoire.',
            ];
        }

        $url = config('services.manypaycash.search_by_external_url', 'https://www.manypaycash.com/api/support/searchTrByExternalId');
        $apiKey = config('services.manypaycash.api_key', 'VWhxZzJiLW5uUGNTbThFLW5DemNSZkJfZ3dTeHNWRmNMUk96aVpjRzIxNTc2MTk4NjE');

        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'ManyPayCash-Support-Admin/1.0',
                ])
                ->post($url, [
                    'external_id' => $externalId,
                ]);

            if ($response->successful()) {
                $rawList = $response->json();
                $formatted = $this->formatTransactionList($rawList, ['external_id' => $externalId]);

                return [
                    'success' => true,
                    'query_type' => 'external_id',
                    'query_id' => $externalId,
                    'count' => count($formatted),
                    'transactions' => $formatted,
                    'message' => count($formatted) > 0
                        ? count($formatted) . ' transaction(s) trouvée(s).'
                        : 'Aucune transaction trouvée pour cet external_id.',
                ];
            }

            Log::warning('Échec recherche transaction ManyPayCash par external_id: ' . $response->status(), [
                'body' => $response->body(),
                'external_id' => $externalId,
            ]);

            return [
                'success' => false,
                'query_type' => 'external_id',
                'query_id' => $externalId,
                'count' => 0,
                'transactions' => [],
                'message' => 'Erreur lors de la communication avec l\'API distante (' . $response->status() . ').',
            ];
        } catch (\Throwable $e) {
            Log::error('Exception recherche transaction par external_id: ' . $e->getMessage(), [
                'external_id' => $externalId,
            ]);

            return [
                'success' => false,
                'query_type' => 'external_id',
                'query_id' => $externalId,
                'count' => 0,
                'transactions' => [],
                'message' => 'Une exception réseau est survenue lors de la recherche.',
            ];
        }
    }

    /**
     * Recherche une transaction par internal_id (GET https://www.manypaycash.com/api/support/searchTrByInternalId/:internal_id)
     */
    public function searchByInternalId(string $internalId): array
    {
        $internalId = trim($internalId);
        if (empty($internalId)) {
            return [
                'success' => false,
                'query_type' => 'internal_id',
                'query_id' => '',
                'count' => 0,
                'transactions' => [],
                'message' => 'L\'identifiant interne (internal_id) est obligatoire.',
            ];
        }

        $baseUrl = rtrim(config('services.manypaycash.search_by_internal_url', 'https://www.manypaycash.com/api/support/searchTrByInternalId'), '/');
        $url = $baseUrl . '/' . urlencode($internalId);
        $apiKey = config('services.manypaycash.api_key', 'VWhxZzJiLW5uUGNTbThFLW5DemNSZkJfZ3dTeHNWRmNMUk96aVpjRzIxNTc2MTk4NjE');

        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'Accept' => 'application/json',
                    'User-Agent' => 'ManyPayCash-Support-Admin/1.0',
                ])
                ->get($url);

            if ($response->successful()) {
                $rawList = $response->json();
                $formatted = $this->formatTransactionList($rawList, ['internal_id' => $internalId]);

                return [
                    'success' => true,
                    'query_type' => 'internal_id',
                    'query_id' => $internalId,
                    'count' => count($formatted),
                    'transactions' => $formatted,
                    'message' => count($formatted) > 0
                        ? count($formatted) . ' transaction(s) trouvée(s).'
                        : 'Aucune transaction trouvée pour cet internal_id.',
                ];
            }

            Log::warning('Échec recherche transaction ManyPayCash par internal_id: ' . $response->status(), [
                'body' => $response->body(),
                'internal_id' => $internalId,
            ]);

            return [
                'success' => false,
                'query_type' => 'internal_id',
                'query_id' => $internalId,
                'count' => 0,
                'transactions' => [],
                'message' => 'Erreur lors de la communication avec l\'API distante (' . $response->status() . ').',
            ];
        } catch (\Throwable $e) {
            Log::error('Exception recherche transaction par internal_id: ' . $e->getMessage(), [
                'internal_id' => $internalId,
            ]);

            return [
                'success' => false,
                'query_type' => 'internal_id',
                'query_id' => $internalId,
                'count' => 0,
                'transactions' => [],
                'message' => 'Une exception réseau est survenue lors de la recherche.',
            ];
        }
    }

    /**
     * Recherche combinée intelligente (teste external_id puis internal_id si vide).
     */
    public function searchAuto(string $identifier): array
    {
        $identifier = trim($identifier);
        if (empty($identifier)) {
            return [
                'success' => false,
                'query_type' => 'auto',
                'query_id' => '',
                'count' => 0,
                'transactions' => [],
                'message' => 'Veuillez saisir un identifiant à rechercher.',
            ];
        }

        // 1. Essai prioritaire par external_id
        $externalResult = $this->searchByExternalId($identifier);
        if (!empty($externalResult['transactions'])) {
            return $externalResult;
        }

        // 2. Si aucune transaction trouvée, essai par internal_id
        $internalResult = $this->searchByInternalId($identifier);
        if (!empty($internalResult['transactions'])) {
            return $internalResult;
        }

        return [
            'success' => true,
            'query_type' => 'auto',
            'query_id' => $identifier,
            'count' => 0,
            'transactions' => [],
            'message' => 'Aucune transaction trouvée ni par external_id ni par internal_id.',
        ];
    }

    /**
     * Formate et normalise la liste brute retournée par l'API.
     */
    protected function formatTransactionList(mixed $rawList, array $extraContext = []): array
    {
        if (!is_array($rawList)) {
            return [];
        }

        // Si l'API retourne un seul objet au lieu d'une liste
        if (isset($rawList['montant']) || isset($rawList['etat'])) {
            $rawList = [$rawList];
        }

        $formatted = [];

        foreach ($rawList as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            // Décodage de info_pay
            $infoPayRaw = $item['info_pay'] ?? null;
            $infoPayParsed = [];
            if (is_string($infoPayRaw)) {
                $decoded = json_decode($infoPayRaw, true);
                if (is_array($decoded)) {
                    $infoPayParsed = $decoded;
                }
            } elseif (is_array($infoPayRaw)) {
                $infoPayParsed = $infoPayRaw;
            }

            $montant = is_numeric($item['montant'] ?? null) ? (float) $item['montant'] : 0.0;
            $frais = is_numeric($item['frais'] ?? null) ? (float) $item['frais'] : 0.0;
            $etat = strtoupper(trim((string) ($item['etat'] ?? 'UNKNOWN')));

            $rawCreatedAt = $item['created_at'] ?? null;
            $createdAtFormatted = null;
            $createdAtHuman = null;

            if ($rawCreatedAt) {
                try {
                    $cDate = Carbon::parse($rawCreatedAt);
                    $createdAtFormatted = $cDate->format('d/m/Y à H:i:s');
                    $createdAtHuman = $cDate->diffForHumans();
                } catch (\Throwable) {
                    $createdAtFormatted = (string) $rawCreatedAt;
                }
            }

            $formatted[] = [
                'index' => $index + 1,
                'external_id' => $item['external_id'] ?? ($extraContext['external_id'] ?? null),
                'internal_id' => $item['internal_id'] ?? ($item['id'] ?? ($extraContext['internal_id'] ?? null)),
                'montant' => $montant,
                'montant_formate' => number_format($montant, 0, ',', ' ') . ' FCFA',
                'frais' => $frais,
                'frais_formate' => number_format($frais, 0, ',', ' ') . ' FCFA',
                'total' => $montant + $frais,
                'total_formate' => number_format($montant + $frais, 0, ',', ' ') . ' FCFA',
                'etat' => $etat,
                'badge' => $this->resolveStatusBadge($etat),
                'info_pay' => [
                    'nom' => $infoPayParsed['nom'] ?? 'Non spécifié',
                    'numero' => $infoPayParsed['numero'] ?? 'Non spécifié',
                    'indicatif' => $infoPayParsed['indicatif'] ?? '',
                    'telephone_complet' => trim(($infoPayParsed['indicatif'] ?? '') . ' ' . ($infoPayParsed['numero'] ?? '')),
                    'raw' => $infoPayRaw,
                ],
                'created_at' => $rawCreatedAt,
                'created_at_formate' => $createdAtFormatted ?? 'Date inconnue',
                'created_at_human' => $createdAtHuman ?? '',
                'raw' => $item,
            ];
        }

        return $formatted;
    }

    /**
     * Détermine les classes visuelles de badge selon le statut.
     */
    protected function resolveStatusBadge(string $status): array
    {
        return match ($status) {
            'SUCCESS', 'SUCCEEDED', 'COMPLETED', 'PAID' => [
                'label' => 'Succès',
                'class' => 'bg-success-50 text-success-700 border border-success-200 dark:bg-success-500/15 dark:text-success-400 dark:border-success-500/30',
                'dot' => 'bg-success-500',
                'icon' => 'check-circle',
            ],
            'FAILED', 'FAILURE', 'REJECTED', 'CANCELLED', 'CANCELED' => [
                'label' => 'Échoué',
                'class' => 'bg-error-50 text-error-700 border border-error-200 dark:bg-error-500/15 dark:text-error-400 dark:border-error-500/30',
                'dot' => 'bg-error-500',
                'icon' => 'x-circle',
            ],
            'PENDING', 'PROCESSING', 'INITIATED' => [
                'label' => 'En cours',
                'class' => 'bg-warning-50 text-warning-700 border border-warning-200 dark:bg-warning-500/15 dark:text-warning-400 dark:border-warning-500/30',
                'dot' => 'bg-warning-500',
                'icon' => 'clock',
            ],
            default => [
                'label' => $status,
                'class' => 'bg-gray-100 text-gray-700 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                'dot' => 'bg-gray-400',
                'icon' => 'question-mark-circle',
            ],
        };
    }
}
