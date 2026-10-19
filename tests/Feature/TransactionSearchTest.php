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
}
