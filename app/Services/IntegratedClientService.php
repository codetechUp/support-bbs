<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IntegratedClientService
{
    protected int $cacheTtl = 60;

    protected string $cacheKey = 'manypaycash_integrated_clients_list';

    public function __construct(
        protected PartnerServiceStatusService $serviceStatusService
    ) {}

    /**
     * Retourne les clients intégrés depuis l'API ManyPayCash.
     * Seules les réponses réussies sont mises en cache.
     */
    public function getClients(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget($this->cacheKey);
        }

        if (! $forceRefresh) {
            $cached = Cache::get($this->cacheKey);

            if (is_array($cached)) {
                return $cached;
            }
        }

        $result = $this->fetchAndFormatClients();

        if (($result['_api_success'] ?? false) === true) {
            Cache::put(
                $this->cacheKey,
                $this->removeInternalFields($result),
                $this->cacheTtl
            );
        }

        return $this->removeInternalFields($result);
    }

    /**
     * Appelle GET /api/support/customers.
     */
    protected function fetchAndFormatClients(): array
    {
        $url = config(
            'services.manypaycash.customers_url',
            'https://www.manypaycash.com/api/support/customers'
        );

        // La clé doit être configurée dans le fichier .env.
        $apiKey = config('services.manypaycash.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            Log::error(
                'API ManyPayCash : la clé API services.manypaycash.api_key est absente.'
            );

            return $this->emptyResult();
        }

        try {
            $response = Http::timeout(15)
                ->connectTimeout(5)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'Accept' => 'application/json',
                    'User-Agent' => 'ManyPayCash-Support-Admin/1.0',
                ])
                ->get($url);

            if (! $response->successful()) {
                Log::warning(
                    'Échec API ManyPayCash customers.',
                    ['http_status' => $response->status()]
                );

                return $this->emptyResult();
            }

            // L'API renvoie directement un tableau JSON.
            $customers = $response->json();

            if (! is_array($customers) || ! $this->isCustomerList($customers)) {
                Log::warning(
                    'Réponse inattendue de l’API ManyPayCash customers.'
                );

                return $this->emptyResult();
            }

            $clients = [];

            foreach ($customers as $customer) {
                $client = $this->formatCustomer($customer);

                if ($client !== null) {
                    $clients[] = $client;
                }
            }

            return [
                'summary' => $this->buildSummary($clients),
                'clients' => $clients,
                '_api_success' => true,
            ];
        } catch (\Throwable $e) {
            // Ne pas journaliser la clé API ni le contenu sensible de la réponse.
            Log::error(
                'Exception lors de l’appel à l’API ManyPayCash customers.',
                ['exception' => $e::class]
            );

            return $this->emptyResult();
        }
    }

    /**
     * Vérifie que la réponse est bien une liste d'objets clients.
     */
    protected function isCustomerList(array $customers): bool
    {
        if ($customers === []) {
            return true;
        }

        foreach ($customers as $customer) {
            if (! is_array($customer) || ! array_key_exists('id', $customer)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convertit un client API vers le format attendu par la vue Blade.
     */
    protected function formatCustomer(array $customer): ?array
    {
        if (! isset($customer['id'])) {
            return null;
        }

        $id = (string) $customer['id'];

        $rawName = trim((string) ($customer['entreprise'] ?? ''));
        $clientName = $rawName !== '' ? $rawName : 'Client #'.$id;

        $accountStatus = strtoupper(
            trim((string) ($customer['etat'] ?? 'UNKNOWN'))
        );

        $environment = $this->formatEnvironment(
            $customer['env'] ?? null
        );

        $rawDate = $customer['createdAt'] ?? null;
        $timestamp = null;
        $formattedDate = 'Inconnue';

        if (is_string($rawDate) && trim($rawDate) !== '') {
            try {
                $date = Carbon::parse($rawDate);
                $timestamp = $date->timestamp;
                $formattedDate = $date->format('d/m/Y à H:i');
            } catch (\Throwable) {
                // La date reste inconnue si le format est invalide.
            }
        }

        /*
         * L'API customers ne fournit pas de relation fiable entre un client
         * et les services de l'API partenaires.
         * Ne pas inventer de services ou de statut opérationnel client.
         */
        return [
            'id' => $id,
            'client_name' => $clientName,

            // Ces champs ne sont pas fournis par l'endpoint customers.
            'email' => null,

            // L'API fournit un id client, pas un Partner ID distinct.
            'partner_id' => $id,

            'account_status' => $accountStatus,
            'environment' => $environment,
            'integration_date' => $formattedDate,
            'integration_timestamp' => $timestamp,

            'services_status' => 'UNKNOWN',
            'used_services' => [],

            'is_remote' => true,
        ];
    }

    /**
     * Normalise l'environnement sans déduire sa valeur de l'identifiant.
     */
    protected function formatEnvironment(mixed $environment): string
    {
        $value = strtolower(trim((string) ($environment ?? '')));

        return match ($value) {
            'prod', 'production' => 'Production',
            'dev', 'development', 'sandbox', 'test' => 'Sandbox',
            default => 'Inconnu',
        };
    }

    /**
     * Construit les statistiques à partir des seuls clients reçus.
     */
    protected function buildSummary(array $clients): array
    {
        $activeCount = collect($clients)
            ->where('account_status', 'ACTIVE')
            ->count();

        return [
            'total_clients' => count($clients),
            'active_clients' => $activeCount,
            'inactive_clients' => count($clients) - $activeCount,
            'production_clients' => collect($clients)
                ->where('environment', 'Production')
                ->count(),
            'sandbox_clients' => collect($clients)
                ->where('environment', 'Sandbox')
                ->count(),
            'unknown_environment_clients' => collect($clients)
                ->where('environment', 'Inconnu')
                ->count(),
            'checked_at' => now()->format('H:i:s'),
        ];
    }

    /**
     * Structure stable en cas d'erreur.
     * Une réponse en échec n'est pas mise en cache.
     */
    protected function emptyResult(): array
    {
        return [
            'summary' => [
                'total_clients' => 0,
                'active_clients' => 0,
                'inactive_clients' => 0,
                'production_clients' => 0,
                'sandbox_clients' => 0,
                'unknown_environment_clients' => 0,
                'checked_at' => now()->format('H:i:s'),
            ],
            'clients' => [],
            '_api_success' => false,
        ];
    }

    /**
     * Retire le marqueur interne avant de retourner les données.
     */
    protected function removeInternalFields(array $result): array
    {
        unset($result['_api_success']);

        return $result;
    }
}
