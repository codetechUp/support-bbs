<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PartnerServiceStatusService
{
    /**
     * Cache TTL in seconds (60 seconds for performance and smooth UI)
     */
    protected int $cacheTtl = 60;

    /**
     * Retrieve partner services status data.
     */
    public function getStatusData(bool $forceRefresh = false): array
    {
        $cacheKey = 'manypaycash_partner_services_status';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->fetchFromExternalApi();
        });
    }

    /**
     * Fetch from external API and process statuses into UP, DOWN, DEGRADED.
     */
    protected function fetchFromExternalApi(): array
    {
        $url = config('services.manypaycash.url', 'https://www.manypaycash.com/api/support/partenaire');
        $apiKey = config('services.manypaycash.api_key', 'VWhxZzJiLW5uUGNTbThFLW5DemNSZkJfZ3dTeHNWRmNMUk96aVpjRzIxNTc2MTk4NjE');

        $startTime = microtime(true);
        $rawPartners = [];
        $isError = false;
        $errorMessage = null;

        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'Accept' => 'application/json',
                    'User-Agent' => 'ManyPayCash-Support-Admin/1.0',
                ])
                ->get($url);

            $latencyMs = round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $rawPartners = $response->json();
                if (!is_array($rawPartners)) {
                    $rawPartners = [];
                }
            } else {
                $isError = true;
                $errorMessage = 'Erreur HTTP ' . $response->status() . ': ' . $response->body();
                Log::warning('Échec appel API ManyPayCash partenaires: ' . $errorMessage);
            }
        } catch (\Throwable $e) {
            $latencyMs = round((microtime(true) - $startTime) * 1000);
            $isError = true;
            $errorMessage = $e->getMessage();
            Log::error('Exception appel API ManyPayCash: ' . $errorMessage);
        }

        // Si l'appel a échoué et que nous n'avons pas de données, utiliser un jeu de secours cohérent
        if ($isError && empty($rawPartners)) {
            $rawPartners = $this->getFallbackData();
        }

        return $this->transformPartnersData($rawPartners, $latencyMs, $isError, $errorMessage);
    }

    /**
     * Transform raw partners and balances into UP, DOWN, DEGRADED status model.
     */
    protected function transformPartnersData(array $rawPartners, int $latencyMs, bool $isError, ?string $errorMessage): array
    {
        $partners = [];
        $services = [];
        $totalServices = 0;
        $upCount = 0;
        $degradedCount = 0;
        $downCount = 0;

        foreach ($rawPartners as $partner) {
            $partnerName = $partner['nom'] ?? 'Service inconnu';
            $balances = $partner['balances'] ?? [];

            $partnerStatus = 'UP';
            $processedBalances = [];
            $serviceCodes = [];

            if (empty($balances)) {
                // Aucun solde actif ou service indisponible
                $partnerStatus = 'DOWN';
                $downCount++;
                $totalServices++;

                $services[] = [
                    'id' => \Illuminate\Support\Str::slug($partnerName . '-none'),
                    'partner_name' => $partnerName,
                    'service_code' => 'N/A',
                    'status' => 'DOWN',
                    'status_balance' => 'EMPTY',
                    'status_label' => 'Indisponible (Aucun solde)',
                    'checked_at' => now()->toIso8601String(),
                ];
            } else {
                $hasLow = false;
                $hasCritical = false;

                foreach ($balances as $balance) {
                    $serviceCode = $balance['service'] ?? 'default';
                    $rawStatus = strtoupper($balance['status_balance'] ?? 'UNKNOWN');
                    $serviceCodes[] = $serviceCode;
                    $totalServices++;

                    $serviceStatus = 'UP';
                    if (in_array($rawStatus, ['LOW', 'DEGRADED'])) {
                        $serviceStatus = 'DEGRADED';
                        $hasLow = true;
                    } elseif (in_array($rawStatus, ['DOWN', 'EMPTY', 'CRITICAL', 'ERROR', 'ZERO'])) {
                        $serviceStatus = 'DOWN';
                        $hasCritical = true;
                    } else {
                        // NORMAL, MEDIUM, etc.
                        $serviceStatus = 'UP';
                    }

                    if ($serviceStatus === 'UP') {
                        $upCount++;
                    } elseif ($serviceStatus === 'DEGRADED') {
                        $degradedCount++;
                    } else {
                        $downCount++;
                    }

                    $processedBalances[] = [
                        'service' => $serviceCode,
                        'status_balance' => $rawStatus,
                        'status' => $serviceStatus,
                        'status_label' => $this->formatStatusLabel($serviceStatus, $rawStatus),
                    ];

                    $services[] = [
                        'id' => \Illuminate\Support\Str::slug($partnerName . '-' . $serviceCode),
                        'partner_name' => $partnerName,
                        'service_code' => $serviceCode,
                        'status' => $serviceStatus,
                        'status_balance' => $rawStatus,
                        'status_label' => $this->formatStatusLabel($serviceStatus, $rawStatus),
                        'checked_at' => now()->toIso8601String(),
                    ];
                }

                if ($hasCritical) {
                    $partnerStatus = 'DOWN';
                } elseif ($hasLow) {
                    $partnerStatus = 'DEGRADED';
                } else {
                    $partnerStatus = 'UP';
                }
            }

            $partners[] = [
                'nom' => $partnerName,
                'status' => $partnerStatus,
                'status_label' => $this->formatPartnerStatusLabel($partnerStatus),
                'services_list' => !empty($serviceCodes) ? implode(', ', $serviceCodes) : 'Aucun service',
                'balances_count' => count($balances),
                'balances' => $processedBalances,
                'has_issues' => $partnerStatus !== 'UP',
            ];
        }

        $globalStatus = 'UP';
        if ($downCount > 0) {
            $globalStatus = 'DEGRADED'; // ou DOWN selon sévérité
            if ($downCount >= count($rawPartners) / 2) {
                $globalStatus = 'DOWN';
            }
        } elseif ($degradedCount > 0) {
            $globalStatus = 'DEGRADED';
        }

        return [
            'summary' => [
                'total_partners' => count($partners),
                'total_services' => $totalServices,
                'up_count' => $upCount,
                'degraded_count' => $degradedCount,
                'down_count' => $downCount,
                'global_status' => $globalStatus,
                'latency_ms' => $latencyMs,
                'checked_at' => now()->toIso8601String(),
                'checked_at_human' => now()->format('H:i:s'),
                'is_fallback' => $isError,
                'error_message' => $errorMessage,
            ],
            'partners' => $partners,
            'services' => $services,
        ];
    }

    /**
     * Format human label for a specific balance/service status.
     */
    protected function formatStatusLabel(string $status, string $rawBalance): string
    {
        return match ($status) {
            'UP' => $rawBalance === 'MEDIUM' ? 'Opérationnel (Solde moyen)' : 'Opérationnel (Solde normal)',
            'DEGRADED' => 'Dégradé (Solde bas)',
            'DOWN' => 'Indisponible',
            default => 'Inconnu',
        };
    }

    /**
     * Format human label for partner card.
     */
    protected function formatPartnerStatusLabel(string $status): string
    {
        return match ($status) {
            'UP' => 'Opérationnel',
            'DEGRADED' => 'Service Dégradé',
            'DOWN' => 'Service Indisponible',
            default => 'Inconnu',
        };
    }

    /**
     * Fallback mock data in case of connection or network failure.
     */
    protected function getFallbackData(): array
    {
        return [
            [
                'nom' => 'Mixx senegal',
                'balances' => [
                    ['service' => 'snmixx', 'status_balance' => 'LOW'],
                ],
            ],
            [
                'nom' => 'Paydeck',
                'balances' => [
                    ['service' => 'wave', 'status_balance' => 'LOW'],
                ],
            ],
            [
                'nom' => 'Orange money senegal',
                'balances' => [
                    ['service' => 'snom', 'status_balance' => 'NORMAL'],
                ],
            ],
            [
                'nom' => 'Remita',
                'balances' => [
                    ['service' => 'MTNCM', 'status_balance' => 'LOW'],
                    ['service' => 'OMCM', 'status_balance' => 'LOW'],
                ],
            ],
            [
                'nom' => 'Bictorys',
                'balances' => [],
            ],
            [
                'nom' => 'Wave cote d\'ivoire',
                'balances' => [
                    ['service' => 'ciwave', 'status_balance' => 'LOW'],
                ],
            ],
            [
                'nom' => 'Wave senegal',
                'balances' => [
                    ['service' => 'snwave', 'status_balance' => 'NORMAL'],
                ],
            ],
        ];
    }
}
