<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleAuthenticationController extends Controller
{
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $email = $googleUser->getEmail();

        if ($email === null) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Email Google tidak tersedia.']);
        }

        $googleId = $googleUser->getId();
        $user = User::query()
            ->when(
                $googleId !== null,
                fn ($query) => $query->where('google_id', $googleId)->orWhere('email', $email),
                fn ($query) => $query->where('email', $email),
            )
            ->first();

        if ($user !== null && ! $user->isActive()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Akun ini sedang dinonaktifkan.']);
        }

        $user ??= User::query()->create([
            'name' => $googleUser->getName() ?: Str::before($email, '@'),
            'email' => $email,
            'password' => Str::random(32),
            'role' => UserRole::Tenant,
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);

        $user->forceFill([
            'google_id' => $googleId,
            'google_avatar' => $googleUser->getAvatar(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
