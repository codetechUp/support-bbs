<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IntegratedClientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'support']);

        Http::fake([
            '*/api/support/partenaire*' => Http::response([
                [
                    'nom' => 'Orange money senegal',
                    'balances' => [
                        ['service' => 'snom', 'status_balance' => 'NORMAL'],
                    ],
                ],
                [
                    'nom' => 'Wave senegal',
                    'balances' => [
                        ['service' => 'snwave', 'status_balance' => 'NORMAL'],
                    ],
                ],
            ], 200),
            '*/api/support/getUsers*' => Http::response([
                'meta' => [
                    'total' => 1,
                    'perPage' => 10,
                    'currentPage' => 1,
                ],
                'data' => [
                    [
                        'id' => 1,
                        'prenom' => 'Magor',
                        'nom' => 'Sy',
                        'email' => 'magorsy3@gmail.com',
                        'etat' => 'ACTIVE',
                        'role' => 'client',
                        'createdAt' => '2026-10-10T09:15:00.000Z',
                    ],
                ],
            ], 200),
        ]);
    }

    public function test_support_user_can_view_integrated_clients_page(): void
    {
        $support = User::factory()->create();
        $support->assignRole('support');

        $response = $this->actingAs($support)->get('/support/clients');

        $response->assertOk();
        $response->assertSee('Liste des Clients Intégrés');
        $response->assertSee('Nom du client');
        $response->assertSee('Partner ID');
        $response->assertSee('Statut Compte');
        $response->assertSee('Environnement');
        $response->assertSee("Date d'intégration", false);
        $response->assertSee('Statut des services');
    }

    public function test_admin_user_can_view_integrated_clients_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/support/clients');

        $response->assertOk();
        $response->assertSee('Liste des Clients Intégrés');
    }

    public function test_guest_cannot_view_integrated_clients_page(): void
    {
        $response = $this->get('/support/clients');

        $response->assertRedirect('/login');
    }

    public function test_clients_data_endpoint_returns_json(): void
    {
        $support = User::factory()->create();
        $support->assignRole('support');

        $response = $this->actingAs($support)->getJson('/support/clients/data?refresh=1');

        $response->assertOk();
        $response->assertJsonStructure([
            'summary' => [
                'total_clients',
                'active_clients',
                'inactive_clients',
                'production_clients',
                'sandbox_clients',
            ],
            'clients',
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data['clients']);

        // Vérifier que chaque client a les champs obligatoires et aucune balance chiffrée
        foreach ($data['clients'] as $client) {
            $this->assertArrayHasKey('client_name', $client);
            $this->assertArrayHasKey('partner_id', $client);
            $this->assertArrayHasKey('account_status', $client);
            $this->assertArrayHasKey('environment', $client);
            $this->assertArrayHasKey('integration_date', $client);
            $this->assertArrayHasKey('services_status', $client);

            // Aucune balance ne doit être exposée
            $this->assertArrayNotHasKey('balance', $client);
            $this->assertArrayNotHasKey('solde', $client);
            $this->assertArrayNotHasKey('amount', $client);
        }
    }

    public function test_api_support_clients_endpoint_returns_json(): void
    {
        $response = $this->getJson('/api/support/clients?refresh=1');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'summary',
            'clients',
        ]);
    }
}
