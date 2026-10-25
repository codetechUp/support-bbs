<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransactionSearchService
{
    /**
     * Recherche une transaction par external_id.
     */
    public function searchByExternalId(string $externalId): array
    {
        $externalId = trim($externalId);

        if ($externalId === '') {
            return $this->emptyResult(
                'external_id',
                '',
                'L’identifiant externe est obligatoire.'
            );
        }

        $url = config('services.manypaycash.search_by_external_url');

        if (! $url) {
            return $this->emptyResult(
                'external_id',
                $externalId,
                'URL de recherche externe non configurée.',
                false
            );
        }

        try {
            $response = $this->apiRequest()
                ->post($url, ['external_id' => $externalId]);

            if (! $response->successful()) {
                Log::warning('Échec recherche ManyPayCash par external_id', [
                    'status' => $response->status(),
                ]);

                return $this->emptyResult(
                    'external_id',
                    $externalId,
                    'Erreur de communication avec l’API distante ('
                        .$response->status().').',
                    false
                );
            }

            $transactions = $this->formatTransactionList(
                $response->json(),
                ['external_id' => $externalId]
            );

            return $this->successResult(
                'external_id',
                $externalId,
                $transactions,
                'Aucune transaction trouvée pour cet external_id.'
            );
        } catch (\Throwable $e) {
            Log::error('Exception recherche ManyPayCash par external_id', [
                'exception' => get_class($e),
            ]);

            return $this->emptyResult(
                'external_id',
                $externalId,
                'Une erreur réseau est survenue.',
                false
            );
        }
    }

    /**
     * Recherche une transaction par internal_id.
     */
    public function searchByInternalId(string $internalId): array
    {
        $internalId = trim($internalId);

        if ($internalId === '') {
            return $this->emptyResult(
                'internal_id',
                '',
                'L’identifiant interne est obligatoire.'
            );
        }

        $baseUrl = config('services.manypaycash.search_by_internal_url');

        if (! $baseUrl) {
            return $this->emptyResult(
                'internal_id',
                $internalId,
                'URL de recherche interne non configurée.',
                false
            );
        }

        $url = rtrim($baseUrl, '/').'/'.rawurlencode($internalId);

        try {
            $response = $this->apiRequest()->get($url);

            if (! $response->successful()) {
                Log::warning('Échec recherche ManyPayCash par internal_id', [
                    'status' => $response->status(),
                ]);

                return $this->emptyResult(
                    'internal_id',
                    $internalId,
                    'Erreur de communication avec l’API distante ('
                        .$response->status().').',
                    false
                );
            }

            $transactions = $this->formatTransactionList(
                $response->json(),
                ['internal_id' => $internalId]
            );

            return $this->successResult(
                'internal_id',
                $internalId,
                $transactions,
                'Aucune transaction trouvée pour cet internal_id.'
            );
        } catch (\Throwable $e) {
            Log::error('Exception recherche ManyPayCash par internal_id', [
                'exception' => get_class($e),
            ]);

            return $this->emptyResult(
                'internal_id',
                $internalId,
                'Une erreur réseau est survenue.',
                false
            );
        }
    }

    /**
     * Recherche combinée : external_id, puis internal_id.
     */
    public function searchAuto(string $identifier): array
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return $this->emptyResult(
                'auto',
                '',
                'Veuillez saisir un identifiant à rechercher.'
            );
        }

        $externalResult = $this->searchByExternalId($identifier);

        if (! empty($externalResult['transactions'])) {
            return $externalResult;
        }

        $internalResult = $this->searchByInternalId($identifier);

        if (! empty($internalResult['transactions'])) {
            return $internalResult;
        }

        // Si les deux recherches échouent techniquement,
        // ne pas annoncer à tort qu'aucune transaction n'existe.
        if (
            ($externalResult['success'] ?? false) === false
            || ($internalResult['success'] ?? false) === false
        ) {
            return $this->emptyResult(
                'auto',
                $identifier,
                'La recherche n’a pas pu aboutir. Vérifiez la connexion à l’API.',
                false
            );
        }

        return $this->emptyResult(
            'auto',
            $identifier,
            'Aucune transaction trouvée ni par external_id ni par internal_id.',
            true
        );
    }

    /**
     * Recherche les transactions d'un client sur une période.
     *
     * API :
     * POST /api/support/customerTransactions
     * {
     *   "id_customer": 1,
     *   "debut": "2026-10-01",
     *   "fin": "2026-10-22"
     * }
     */
    public function searchCustomerTransactions(
        int $customerId,
        string $startDate,
        string $endDate
    ): array {
        $queryId = (string) $customerId;

        if ($customerId < 1) {
            return $this->emptyResult(
                'customer_period',
                $queryId,
                'L’identifiant du client doit être positif.'
            );
        }

        try {
            $start = Carbon::createFromFormat('!Y-m-d', $startDate);
            $end = Carbon::createFromFormat('!Y-m-d', $endDate);

            if (
                ! $start
                || ! $end
                || $start->format('Y-m-d') !== $startDate
                || $end->format('Y-m-d') !== $endDate
            ) {
                throw new \InvalidArgumentException('Date invalide.');
            }

            if ($end->lt($start)) {
                return $this->emptyResult(
                    'customer_period',
                    $queryId,
                    'La date de fin doit être postérieure ou égale à la date de début.'
                );
            }
        } catch (\Throwable) {
            return $this->emptyResult(
                'customer_period',
                $queryId,
                'Dates invalides. Le format attendu est AAAA-MM-JJ.'
            );
        }

        $url = config('services.manypaycash.customer_transactions_url');

        if (! $url) {
            return $this->emptyResult(
                'customer_period',
                $queryId,
                'URL des transactions clients non configurée.',
                false
            );
        }

        try {
            $response = $this->apiRequest()
                ->post($url, [
                    'id_customer' => $customerId,
                    'debut' => $startDate,
                    'fin' => $endDate,
                ]);

            if (! $response->successful()) {
                Log::warning('Échec recherche transactions par client et période', [
                    'status' => $response->status(),
                    'customer_id' => $customerId,
                ]);

                return $this->emptyResult(
                    'customer_period',
                    $queryId,
                    'Erreur de communication avec l’API distante ('
                        .$response->status().').',
                    false
                );
            }

            $transactions = $this->formatTransactionList(
                $response->json(),
                ['customer_id' => $customerId]
            );

            return $this->successResult(
                'customer_period',
                $queryId,
                $transactions,
                'Aucune transaction trouvée pour ce client sur cette période.',
                [
                    'customer_id' => $customerId,
                    'date_debut' => $startDate,
                    'date_fin' => $endDate,
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Exception recherche transactions par client et période', [
                'exception' => get_class($e),
                'customer_id' => $customerId,
            ]);

            return $this->emptyResult(
                'customer_period',
                $queryId,
                'Une erreur réseau est survenue lors de la recherche.',
                false
            );
        }
    }

    public function getCustomers(): array
    {
        $url = config('services.manypaycash.customers_url');

        if (! is_string($url) || trim($url) === '') {
            throw new \RuntimeException(
                'URL de récupération des clients ManyPayCash non configurée.'
            );
        }

        $response = $this->apiRequest()->get($url);

        if (! $response->successful()) {
            Log::warning(
                'Échec de récupération des clients ManyPayCash',
                ['status' => $response->status()]
            );

            throw new \RuntimeException(
                'Impossible de récupérer la liste des clients ManyPayCash.'
            );
        }

        $payload = $response->json();

        // L'endpoint peut renvoyer directement une liste ou une liste dans "data".
        if (isset($payload['data']) && is_array($payload['data'])) {
            $payload = $payload['data'];
        }

        if (! is_array($payload)) {
            throw new \RuntimeException(
                'Format de réponse invalide pour la liste des clients.'
            );
        }

        return collect($payload)
            ->filter(fn ($customer) => is_array($customer))
            ->map(fn (array $customer) => [
                'id' => $customer['id'] ?? null,
                'entreprise' => $customer['entreprise'] ?? null,
                'etat' => $customer['etat'] ?? null,
                'env' => $customer['env'] ?? null,
                'createdAt' => $customer['createdAt'] ?? null,
            ])
            ->filter(fn (array $customer) => filter_var($customer['id'], FILTER_VALIDATE_INT) !== false
                && (int) $customer['id'] > 0
            )
            ->values()
            ->all();
    }

    /**
     * Prépare les en-têtes communs aux appels API.
     */
    protected function apiRequest()
    {
        $apiKey = config('services.manypaycash.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new \RuntimeException('Clé API ManyPayCash non configurée.');
        }

        return Http::timeout(15)
            ->connectTimeout(5)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => 'ManyPayCash-Support-Admin/1.0',
            ]);
    }

    /**
     * Normalise les réponses API, y compris les champs camelCase.
     *
     * Ne retourne jamais les soldes de compte ni l'objet brut.
     */
    protected function formatTransactionList(
        mixed $rawList,
        array $extraContext = []
    ): array {
        if (! is_array($rawList)) {
            return [];
        }

        // Certaines API renvoient { "data": [...] }.
        if (isset($rawList['data']) && is_array($rawList['data'])) {
            $rawList = $rawList['data'];
        } elseif (
            isset($rawList['transactions'])
            && is_array($rawList['transactions'])
        ) {
            $rawList = $rawList['transactions'];
        }

        // L'API peut renvoyer une seule transaction.
        if (
            isset($rawList['montant'])
            || isset($rawList['etat'])
            || isset($rawList['externalId'])
        ) {
            $rawList = [$rawList];
        }

        $formatted = [];

        foreach ($rawList as $item) {
            if (! is_array($item)) {
                continue;
            }

            $infoPayRaw = $item['info_pay'] ?? $item['infoPay'] ?? null;
            $infoPay = [];

            if (is_string($infoPayRaw)) {
                $decoded = json_decode($infoPayRaw, true);

                if (is_array($decoded)) {
                    $infoPay = $decoded;
                }
            } elseif (is_array($infoPayRaw)) {
                $infoPay = $infoPayRaw;
            }

            $montant = is_numeric($item['montant'] ?? null)
                ? (float) $item['montant']
                : 0.0;

            $frais = is_numeric($item['frais'] ?? null)
                ? (float) $item['frais']
                : 0.0;

            $etat = strtoupper(trim((string) ($item['etat'] ?? 'UNKNOWN')));

            $createdAt = $item['created_at'] ?? $item['createdAt'] ?? null;
            $createdAtFormatted = 'Date inconnue';
            $createdAtHuman = '';

            if ($createdAt) {
                try {
                    $date = Carbon::parse($createdAt);
                    $createdAtFormatted = $date->format('d/m/Y à H:i:s');
                    $createdAtHuman = $date->diffForHumans();
                } catch (\Throwable) {
                    $createdAtFormatted = (string) $createdAt;
                }
            }

            $numero = $infoPay['numero'] ?? null;
            $indicatif = $infoPay['indicatif'] ?? '';

            $formatted[] = [
                'external_id' => $item['external_id']
                    ?? $item['externalId']
                    ?? ($extraContext['external_id'] ?? null),

                'internal_id' => $item['internal_id']
                    ?? $item['internalId']
                    ?? $item['id']
                    ?? ($extraContext['internal_id'] ?? null),

                'customer_id' => $item['idCustomer']
                    ?? $item['customer_id']
                    ?? ($extraContext['customer_id'] ?? null),

                'partner' => $item['partenaire'] ?? $item['partner'] ?? null,

                'partner_transaction_id' => $item['idTrPartenaire']
                    ?? $item['id_partner']
                    ?? null,

                'service_code' => $item['codeService'] ?? $item['service_code'] ?? null,
                'country_code' => $item['codePays'] ?? $item['country_code'] ?? null,
                'currency_code' => $item['currencyCode'] ?? $item['currency_code'] ?? 'XOF',
                'service_type' => $item['typeService'] ?? $item['service_type'] ?? null,
                'transaction_type' => $item['typeTransaction'] ?? $item['transaction_type'] ?? null,

                'montant' => $montant,
                'montant_formate' => number_format($montant, 0, ',', ' ').' FCFA',

                'frais' => $frais,
                'frais_formate' => number_format($frais, 0, ',', ' ').' FCFA',

                'total' => $montant + $frais,
                'total_formate' => number_format($montant + $frais, 0, ',', ' ').' FCFA',

                'etat' => $etat,
                'badge' => $this->resolveStatusBadge($etat),

                'info_pay' => [
                    'nom' => $infoPay['nom'] ?? 'Non spécifié',
                    'numero' => $numero ?? 'Non spécifié',
                    'indicatif' => $indicatif,
                    'telephone_complet' => trim(
                        $indicatif.' '.($numero ?? '')
                    ),
                ],

                'created_at' => $createdAt,
                'created_at_formate' => $createdAtFormatted,
                'created_at_human' => $createdAtHuman,

                'message_erreur' => $item['msgError']
                    ?? $item['message_erreur']
                    ?? null,
            ];
        }

        return $formatted;
    }

    /**
     * Structure standard d'une recherche réussie.
     */
    protected function successResult(
        string $queryType,
        string $queryId,
        array $transactions,
        string $emptyMessage,
        array $extra = []
    ): array {
        $count = count($transactions);

        return array_merge([
            'success' => true,
            'query_type' => $queryType,
            'query_id' => $queryId,
            'count' => $count,
            'transactions' => $transactions,
            'message' => $count > 0
                ? $count.' transaction(s) trouvée(s).'
                : $emptyMessage,
        ], $extra);
    }

    /**
     * Structure standard d'une recherche sans résultat ou en erreur.
     */
    protected function emptyResult(
        string $queryType,
        string $queryId,
        string $message,
        bool $success = false
    ): array {
        return [
            'success' => $success,
            'query_type' => $queryType,
            'query_id' => $queryId,
            'count' => 0,
            'transactions' => [],
            'message' => $message,
        ];
    }

    /**
     * Détermine le badge correspondant au statut.
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

            'TIMED_OUT', 'TIMEOUT', 'TIMEDOUT' => [
                'label' => 'Expiré',
                'class' => 'bg-error-50 text-error-700 border border-error-200 dark:bg-error-500/15 dark:text-error-400 dark:border-error-500/30',
                'dot' => 'bg-error-500',
                'icon' => 'clock',
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
