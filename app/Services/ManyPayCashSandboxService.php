<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ManyPayCashSandboxService
{
    private const BASE_URL = 'https://www.manypaycash.com/api/support/sandbox';

    /**
     * Liste des clients Sandbox.
     */
    public function listeCustomers(int $page = 1, int $limit = 10): array
    {
        $apiKey = config('services.manypaycash.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('La clé API ManyPayCash est absente de la configuration.');
        }

        $response = Http::timeout(20)
            ->connectTimeout(10)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post(self::BASE_URL.'/listeCustomer', [
                'page' => max(1, $page),
                'limit' => max(1, $limit),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Erreur ManyPayCash ('.$response->status().').');
        }

        $result = $response->json();

        if (! is_array($result)) {
            throw new RuntimeException('La réponse de ManyPayCash est invalide.');
        }

        return $result;
    }

    /**
     * Création d'un client Sandbox.
     */
    public function createCustomer(array $data): array
    {
        $apiKey = config('services.manypaycash.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('La clé API ManyPayCash est absente de la configuration.');
        }

        $response = Http::timeout(20)
            ->connectTimeout(10)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post(self::BASE_URL.'/createCustomer', [
                'entreprise' => $data['entreprise'],
                'prenom' => $data['prenom'],
                'nom' => $data['nom'],
                'email' => $data['email'],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Erreur ManyPayCash ('.$response->status().').');
        }

        $result = $response->json();

        if (! is_array($result)) {
            throw new RuntimeException('La réponse de ManyPayCash est invalide.');
        }

        return $result;
    }

    /**
     * Liste des utilisateurs Sandbox.
     */
    public function listeUsers(int $page = 1, int $limit = 100): array
    {
        $apiKey = config('services.manypaycash.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('La clé API ManyPayCash est absente de la configuration.');
        }

        $response = Http::timeout(20)
            ->connectTimeout(10)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post(self::BASE_URL.'/users', [
                'page' => max(1, $page),
                'limit' => max(1, min(100, $limit)),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Erreur ManyPayCash ('.$response->status().').'
            );
        }

        $result = $response->json();

        if (! is_array($result)) {
            throw new RuntimeException('La réponse de ManyPayCash est invalide.');
        }

        return $result;
    }

    /**
     * Récupère les informations détaillées d'un utilisateur Sandbox par e-mail.
     */
    public function getUserByEmail(string $email): array
    {
        $apiKey = config('services.manypaycash.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('La clé API ManyPayCash est absente de la configuration.');
        }

        $response = Http::timeout(20)
            ->connectTimeout(10)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->get(self::BASE_URL.'/user/'.rawurlencode($email));

        if (! $response->successful()) {
            throw new RuntimeException(
                'Erreur ManyPayCash ('.$response->status().').'
            );
        }

        $result = $response->json();

        if (! is_array($result)) {
            throw new RuntimeException('La réponse de ManyPayCash est invalide.');
        }

        return $result;
    }

    public function getUsersByCustomer(int $idCustomer): array
    {
        $apiKey = config('services.manypaycash.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException(
                'La clé API ManyPayCash est absente de la configuration.'
            );
        }

        if ($idCustomer < 1) {
            throw new RuntimeException('Identifiant client invalide.');
        }

        $response = Http::timeout(20)
            ->connectTimeout(10)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->get(self::BASE_URL.'/users/customer/'.$idCustomer);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Erreur ManyPayCash ('.$response->status().').'
            );
        }

        $result = $response->json();

        if (! is_array($result)) {
            throw new RuntimeException(
                'La réponse de ManyPayCash est invalide.'
            );
        }

        return $result;
    }

    /**
     * Récupère les transactions Sandbox.
     *
     * @param  array  $filters  Filtres facultatifs de recherche.
     */
    public function listeTransactions(array $filters = []): array
    {
        $apiKey = config('services.manypaycash.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException(
                'La clé API ManyPayCash est absente de la configuration.'
            );
        }

        $allowedFilters = [
            'debut',
            'fin',
            'page',
            'limit',
            'external_id',
            'id_customer',
        ];

        // Ne transmettre que les paramètres autorisés et renseignés.
        $payload = array_intersect_key($filters, array_flip($allowedFilters));

        $payload = array_filter(
            $payload,
            fn ($value) => $value !== null && $value !== ''
        );

        $response = Http::timeout(20)
            ->connectTimeout(10)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post(self::BASE_URL.'/transactions', $payload);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Erreur ManyPayCash ('.$response->status().').'
            );
        }

        $result = $response->json();

        if (! is_array($result)) {
            throw new RuntimeException(
                'La réponse de ManyPayCash est invalide.'
            );
        }

        return $result;
    }


    /**
     * Déclenche l'endpoint d'envoi des identifiants d'un client Sandbox.
     */
    public function sendCredentials(string $email): array
    {
        $apiKey = config('services.manypaycash.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException(
                'La clé API ManyPayCash est absente de la configuration.'
            );
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Adresse e-mail invalide.');
        }

        $response = Http::timeout(20)
            ->connectTimeout(10)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->get(self::BASE_URL.'/sendCredentials/'.$email);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Erreur ManyPayCash ('.$response->status().').'
            );
        }

        $result = $response->json();

        if (! is_array($result)) {
            throw new RuntimeException(
                'La réponse de ManyPayCash est invalide.'
            );
        }

        return $result;
    }

}
