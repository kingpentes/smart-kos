<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_list(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $user = User::factory()->create(['name' => 'John Doe']);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('John Doe');
    }

    public function test_non_admin_cannot_view_users_list(): void
    {
        $tenant = User::factory()->create(['role' => UserRole::Tenant]);

        $response = $this->actingAs($tenant)->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_suspend_user(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $user = User::factory()->create(['status' => UserStatus::Active]);

        $response = $this->actingAs($admin)->patch(route('admin.users.toggle-status', $user));
        
        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertEquals(UserStatus::Suspended, $user->fresh()->status);
    }

    public function test_admin_cannot_suspend_themselves(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);

        $response = $this->actingAs($admin)->patch(route('admin.users.toggle-status', $admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals(UserStatus::Active, $admin->fresh()->status);
    }
}
