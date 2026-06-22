<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_redirected_to_google(): void
    {
        $provider = Mockery::mock();
        $provider
            ->shouldReceive('redirect')
            ->once()
            ->andReturn(new RedirectResponse('https://accounts.google.com/o/oauth2/v2/auth'));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->get(route('auth.google.redirect'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/v2/auth');
    }

    public function test_google_callback_creates_new_tenant_user(): void
    {
        $this->fakeGoogleCallback($this->fakeGoogleUser(
            id: 'google-123',
            name: 'Google Tenant',
            email: 'google-tenant@example.com',
            avatar: 'https://example.com/avatar.png',
        ));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $user = User::query()->where('email', 'google-tenant@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'email' => 'google-tenant@example.com',
            'google_id' => 'google-123',
            'google_avatar' => 'https://example.com/avatar.png',
            'role' => UserRole::Tenant->value,
            'status' => UserStatus::Active->value,
        ]);
    }

    public function test_google_callback_links_existing_active_user_by_email(): void
    {
        $owner = User::factory()->owner()->create([
            'email' => 'owner@example.com',
            'google_id' => null,
        ]);

        $this->fakeGoogleCallback($this->fakeGoogleUser(
            id: 'google-owner',
            name: 'Owner Google',
            email: 'owner@example.com',
            avatar: null,
        ));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($owner);
        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'email' => 'owner@example.com',
            'google_id' => 'google-owner',
            'role' => UserRole::Owner->value,
        ]);
    }

    public function test_suspended_user_cannot_login_with_google(): void
    {
        User::factory()->tenant()->suspended()->create([
            'email' => 'blocked@example.com',
            'google_id' => null,
        ]);

        $this->fakeGoogleCallback($this->fakeGoogleUser(
            id: 'google-blocked',
            name: 'Blocked User',
            email: 'blocked@example.com',
            avatar: null,
        ));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => 'blocked@example.com',
            'google_id' => null,
        ]);
    }

    private function fakeGoogleCallback(SocialiteUser $googleUser): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);
    }

    private function fakeGoogleUser(string $id, string $name, ?string $email, ?string $avatar): SocialiteUser
    {
        $user = Mockery::mock(SocialiteUser::class);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('getName')->andReturn($name);
        $user->shouldReceive('getEmail')->andReturn($email);
        $user->shouldReceive('getAvatar')->andReturn($avatar);

        return $user;
    }
}
