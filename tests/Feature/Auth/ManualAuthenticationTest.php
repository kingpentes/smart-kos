<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_register_with_manual_authentication(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Calon Penyewa',
            'email' => 'tenant@example.com',
            'phone' => '08123456789',
            'role' => UserRole::Tenant->value,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'tenant@example.com',
            'role' => UserRole::Tenant->value,
            'status' => UserStatus::Active->value,
        ]);
    }

    public function test_owner_can_login_and_is_redirected_to_dashboard(): void
    {
        $owner = User::factory()->owner()->create([
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($owner);
    }

    public function test_suspended_user_cannot_login(): void
    {
        User::factory()->tenant()->suspended()->create([
            'email' => 'blocked@example.com',
            'password' => 'password',
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'blocked@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
