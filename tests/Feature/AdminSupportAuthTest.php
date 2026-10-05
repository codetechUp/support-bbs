<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSupportAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_to_login_from_root(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_support_dashboard(): void
    {
        $response = $this->get('/support/dashboard');
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_login_and_is_redirected_to_admin_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_support_can_login_and_is_redirected_to_support_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'support@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('support.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_support_cannot_access_admin_routes(): void
    {
        $support = User::where('email', 'support@example.com')->first();

        $response = $this->actingAs($support)->get('/admin/dashboard');
        $response->assertForbidden();

        $response = $this->actingAs($support)->get('/admin/supports/create');
        $response->assertForbidden();
    }

    public function test_admin_can_view_dashboard_and_creation_form(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertOk();
        $response->assertSee('Tableau de Bord Administrateur');

        $response = $this->actingAs($admin)->get('/admin/supports/create');
        $response->assertOk();
        $response->assertSee('Créer un nouvel agent Support');
    }

    public function test_admin_can_create_a_support_user(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        $response = $this->actingAs($admin)->post('/admin/supports', [
            'name' => 'Nouveau Support Test',
            'email' => 'nouveau.support@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');

        $newUser = User::where('email', 'nouveau.support@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->hasRole('support'));
        $this->assertFalse($newUser->hasRole('admin'));
    }

    public function test_user_can_logout(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        $response = $this->actingAs($admin)->post('/logout');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
