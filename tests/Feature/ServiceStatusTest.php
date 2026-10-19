<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServiceStatusTest extends TestCase
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
                    'nom' => 'Mixx senegal',
                    'balances' => [
                        ['service' => 'snmixx', 'status_balance' => 'LOW'],
                    ],
                ],
                [
                    'nom' => 'Orange money senegal',
                    'balances' => [
                        ['service' => 'snom', 'status_balance' => 'NORMAL'],
                    ],
                ],
                [
                    'nom' => 'Bictorys',
                    'balances' => [],
                ],
            ], 200),
        ]);
    }

    public function test_api_returns_service_status(): void
    {
        $response = $this->getJson('/api/services/status?refresh=1');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'summary' => [
                'total_partners',
                'total_services',
                'up_count',
                'degraded_count',
                'down_count',
                'global_status',
            ],
            'services',
            'partners',
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data['services']);

        foreach ($data['services'] as $service) {
            $this->assertContains($service['status'], ['UP', 'DOWN', 'DEGRADED']);
        }
    }

    public function test_api_flat_format_returns_services_list(): void
    {
        $response = $this->getJson('/api/services/status?format=flat&refresh=1');

        $response->assertOk();
        $services = $response->json();
        $this->assertIsArray($services);
        $this->assertNotEmpty($services);

        foreach ($services as $service) {
            $this->assertArrayHasKey('status', $service);
            $this->assertContains($service['status'], ['UP', 'DOWN', 'DEGRADED']);
        }
    }

    public function test_support_user_can_access_services_dashboard(): void
    {
        $support = User::factory()->create();
        $support->assignRole('support');

        $response = $this->actingAs($support)->get('/support/dashboard');

        $response->assertOk();
        $response->assertSee('Disponibilité des Services Partenaires');
    }

    public function test_admin_user_can_access_services_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/support/dashboard');

        $response->assertOk();
        $response->assertSee('Disponibilité des Services Partenaires');
    }
}
