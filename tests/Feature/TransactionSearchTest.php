<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TransactionSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TransactionSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $supportUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'support', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->supportUser = User::factory()->create([
            'email' => 'support@manypaycash.com',
        ]);
        $this->supportUser->assignRole('support');
    }

    public function test_guest_cannot_access_transactions_page(): void
    {
        $response = $this->get(route('support.transactions.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_support_user_can_access_transactions_page(): void
    {
        $response = $this->actingAs($this->supportUser)
            ->get(route('support.transactions.index'));

        $response->assertStatus(200);
        $response->assertSee('Recherche de Transactions');
        $response->assertSee('external_id');
        $response->assertSee('internal_id');
    }

    public function test_search_by_external_id_endpoint_with_mocked_success(): void
    {
        Http::fake([
            'https://www.manypaycash.com/api/support/searchTrByExternalId*' => Http::response([
                [
                    'montant' => 25000,
                    'frais' => 0,
                    'info_pay' => json_encode([
                        'indicatif' => '+221',
                        'numero' => '788915356',
                        'nom' => 'Mballo Yaya',
                    ]),
                    'etat' => 'FAILED',
                    'created_at' => '2026-10-18T12:27:19.000Z',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->supportUser)
            ->postJson(route('support.transactions.search'), [
                'id' => '916c0a0f-939c-4fc8-93c1-89f25c675718',
                'type' => 'external_id',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 1,
            'query_type' => 'external_id',
            'query_id' => '916c0a0f-939c-4fc8-93c1-89f25c675718',
        ]);

        $response->assertJsonPath('transactions.0.montant', 25000);
        $response->assertJsonPath('transactions.0.etat', 'FAILED');
        $response->assertJsonPath('transactions.0.info_pay.nom', 'Mballo Yaya');
        $response->assertJsonPath('transactions.0.info_pay.numero', '788915356');
    }

    public function test_get_search_tr_by_internal_id_endpoint(): void
    {
        Http::fake([
            'https://www.manypaycash.com/api/support/searchTrByInternalId/INTERNAL-456*' => Http::response([
                [
                    'id' => 'INTERNAL-456',
                    'montant' => 50000,
                    'frais' => 500,
                    'info_pay' => [
                        'indicatif' => '+221',
                        'numero' => '771234567',
                        'nom' => 'Amadou Diallo',
                    ],
                    'etat' => 'SUCCESS',
                    'created_at' => '2026-10-18T10:00:00.000Z',
                ],
            ], 200),
        ]);

        $response = $this->getJson('/searchTrByInternalId/INTERNAL-456');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 1,
            'query_type' => 'internal_id',
            'query_id' => 'INTERNAL-456',
        ]);
        $response->assertJsonPath('transactions.0.montant', 50000);
        $response->assertJsonPath('transactions.0.etat', 'SUCCESS');
        $response->assertJsonPath('transactions.0.info_pay.nom', 'Amadou Diallo');
    }

    public function test_search_by_external_id_empty_validation(): void
    {
        $response = $this->actingAs($this->supportUser)
            ->postJson(route('support.transactions.search'), [
                'id' => '',
                'type' => 'external_id',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_api_route_search_tr_by_internal_id(): void
    {
        Http::fake([
            'https://www.manypaycash.com/api/support/searchTrByInternalId/UNKNOWN-999*' => Http::response([], 200),
        ]);

        $response = $this->getJson('/api/searchTrByInternalId/UNKNOWN-999');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 0,
            'transactions' => [],
        ]);
    }

    public function test_support_user_sees_dual_search_tabs_and_customer_autocomplete(): void
    {
        Http::fake([
            'https://www.manypaycash.com/api/support/customers*' => Http::response([
                ['id' => 12, 'entreprise' => 'Acme Corp', 'etat' => 'ACTIVE', 'env' => 'prod'],
            ], 200),
        ]);

        $response = $this->actingAs($this->supportUser)
            ->get(route('support.transactions.index'));

        $response->assertStatus(200);
        $response->assertSee('Recherche par Référence');
        $response->assertSee('Recherche par Client & Période', false);
        $response->assertSee('customer-date-picker');
    }

    public function test_customer_transactions_search_endpoint_with_mocked_success(): void
    {
        Http::fake([
            'https://www.manypaycash.com/api/support/customerTransactions*' => Http::response([
                [
                    'external_id' => 'EXT-CUST-001',
                    'internal_id' => 'INT-CUST-001',
                    'idCustomer' => 12,
                    'partenaire' => 'remita',
                    'id_tr_partenaire' => '73c0ea24-0ba4-4502-8ae6-1842c8fbe8d6',
                    'montant' => 75000,
                    'frais' => 1500,
                    'etat' => 'SUCCESS',
                    'createdAt' => '2026-10-15T14:30:00.000Z',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->supportUser)
            ->postJson(route('support.transactions.customer-search'), [
                'id_customer' => 12,
                'debut' => '2026-10-01',
                'fin' => '2026-10-20',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 1,
            'query_type' => 'customer_period',
        ]);
        $response->assertJsonPath('transactions.0.montant', 75000);
        $response->assertJsonPath('transactions.0.etat', 'SUCCESS');
        $response->assertJsonPath('transactions.0.partner', 'remita');
        $response->assertJsonPath('transactions.0.id_partner', '73c0ea24-0ba4-4502-8ae6-1842c8fbe8d6');
    }

    public function test_customer_transactions_search_validation_fails_on_invalid_dates(): void
    {
        $response = $this->actingAs($this->supportUser)
            ->postJson(route('support.transactions.customer-search'), [
                'id_customer' => 12,
                'debut' => '2026-10-20',
                'fin' => '2026-10-01', // fin < debut
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['fin']);
    }

    public function test_support_user_can_verify_transaction_at_partner_via_support_route(): void
    {
        $partnerUrl = config('services.manypaycash.search_by_partner_url');

        Http::fake([
            $partnerUrl => Http::response([
                'status' => '00',
                'partner' => 'remita',
                'id_partner' => '73c0ea24-0ba4-4502-8ae6-1842c8fbe8d6',
                'partner_status' => 'SUCCESSFUL',
                'message' => 'Transaction found and confirmed.',
            ], 200),
        ]);

        $response = $this->actingAs($this->supportUser)
            ->postJson(route('support.transactions.verify-partner'), [
                'partner' => 'remita',
                'id_partner' => '73c0ea24-0ba4-4502-8ae6-1842c8fbe8d6',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'partner' => 'remita',
            'id_partner' => '73c0ea24-0ba4-4502-8ae6-1842c8fbe8d6',
            'data' => [
                'partner_status' => 'SUCCESSFUL',
            ],
        ]);
    }

    public function test_direct_post_search_tr_by_partner_id_endpoint_works(): void
    {
        $partnerUrl = config('services.manypaycash.search_by_partner_url');

        Http::fake([
            $partnerUrl => Http::response([
                'id' => 'cos-26hva2prg1xr2',
                'payment_status' => 'succeeded',
            ], 200),
        ]);

        $response = $this->postJson('/searchTrByPartnerId', [
            'partner' => 'wave',
            'id_partner' => 'cos-26hva2prg1xr2',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'partner' => 'snwave',
            'id_partner' => 'cos-26hva2prg1xr2',
        ]);

        // Test avec service_code explicite
        $response2 = $this->postJson('/searchTrByPartnerId', [
            'service_code' => 'snwave',
            'id_partner' => 'cos-26hva2prg1xr2',
        ]);

        $response2->assertOk();
        $response2->assertJson([
            'success' => true,
            'partner' => 'snwave',
            'id_partner' => 'cos-26hva2prg1xr2',
        ]);
    }

    public function test_partner_verification_requires_partner_and_id_partner(): void
    {
        $response = $this->actingAs($this->supportUser)
            ->postJson(route('support.transactions.verify-partner'), [
                'partner' => '',
                'id_partner' => '',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_partner_verification_handles_partner_api_failure(): void
    {
        $partnerUrl = config('services.manypaycash.search_by_partner_url');

        Http::fake([
            $partnerUrl => Http::response([
                'error' => 'Transaction not found on partner system',
            ], 404),
        ]);

        $response = $this->actingAs($this->supportUser)
            ->postJson(route('support.transactions.verify-partner'), [
                'partner' => 'remita',
                'id_partner' => 'unknown-ref',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => false,
            'http_status' => 404,
        ]);
    }

    public function test_support_clients_page_has_shortcut_to_client_transactions(): void
    {
        $response = $this->actingAs($this->supportUser)->get(route('support.clients.index'));

        $response->assertOk();
        $response->assertSee('support/transactions?id_customer=', false);
        $response->assertSee('Transactions');
    }
}
