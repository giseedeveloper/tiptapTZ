<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\SocialAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class SocialAuthController extends Controller
{
    public function redirect(Request $request, string $provider): RedirectResponse|SymfonyRedirectResponse
    {
        $validated = $request->validate([
            'role' => 'nullable|in:manager,waiter',
            'intent' => 'nullable|in:login,register',
        ]);

        if (! in_array($provider, SocialAuth::visibleProviders(), true)) {
            abort(404);
        }

        if (! SocialAuth::providerEnabled($provider)) {
            return back()->with('social_notice', SocialAuth::providerLabel($provider).' sign-in is coming soon.');
        }

        $request->session()->put('oauth', [
            'provider' => $provider,
            'role' => $validated['role'] ?? SocialAuth::ROLE_MANAGER,
            'intent' => $validated['intent'] ?? SocialAuth::INTENT_LOGIN,
        ]);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        if (! SocialAuth::providerEnabled($provider)) {
            return redirect()->route('login')->with('social_notice', 'This sign-in method is not available yet.');
        }

        $context = session('oauth', []);
        session()->forget('oauth');

        $role = $context['role'] ?? SocialAuth::ROLE_MANAGER;
        $intent = $context['intent'] ?? SocialAuth::INTENT_LOGIN;

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable $exception) {
            report($exception);

            return $this->redirectBackForContext($role, $intent)
                ->withErrors(['email' => 'Social sign-in failed. Please try again or use email.']);
        }

        if (! $socialUser->getEmail()) {
            return $this->redirectBackForContext($role, $intent)
                ->withErrors(['email' => 'We could not read your email from '.SocialAuth::providerLabel($provider).'.']);
        }

        $existing = User::query()
            ->where('email', $socialUser->getEmail())
            ->first();

        if ($intent === SocialAuth::INTENT_LOGIN) {
            return $this->handleLogin($existing, $socialUser, $provider);
        }

        return $this->handleRegister($existing, $socialUser, $provider, $role);
    }

    private function handleLogin(?User $existing, $socialUser, string $provider): RedirectResponse
    {
        if (! $existing) {
            return redirect()->route('login')
                ->withErrors(['email' => 'No account found for this email. Please register first.']);
        }

        if ($existing->usesPasswordAuth()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'This account uses email and password. Sign in below with your password.'])
                ->withInput(['email' => $existing->email]);
        }

        if ($existing->auth_provider !== $provider) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Please sign in with '.SocialAuth::providerLabel($existing->auth_provider ?? 'social').'.']);
        }

        $this->syncSocialProfile($existing, $socialUser, $provider);
        Auth::login($existing, remember: true);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function handleRegister(?User $existing, $socialUser, string $provider, string $role): RedirectResponse
    {
        $spatieRole = SocialAuth::roleToSpatie($role);

        if ($existing) {
            if ($existing->usesPasswordAuth()) {
                return $this->redirectBackForContext($role, SocialAuth::INTENT_REGISTER)
                    ->withErrors(['email' => 'An account with this email already exists. Sign in with your password instead.'])
                    ->withInput(['email' => $existing->email]);
            }

            if ($existing->hasRole('manager') && $role === SocialAuth::ROLE_WAITER) {
                return $this->redirectBackForContext($role, SocialAuth::INTENT_REGISTER)
                    ->withErrors(['email' => 'This email is already registered as a restaurant manager.']);
            }

            if ($existing->hasRole('waiter') && $role === SocialAuth::ROLE_MANAGER) {
                return $this->redirectBackForContext($role, SocialAuth::INTENT_REGISTER)
                    ->withErrors(['email' => 'This email is already registered as a waiter.']);
            }

            $this->syncSocialProfile($existing, $socialUser, $provider);
            Auth::login($existing, remember: true);
            request()->session()->regenerate();

            return $this->redirectAfterRegister($existing, $role);
        }

        $user = User::create([
            'name' => $socialUser->getName() ?: Str::before($socialUser->getEmail(), '@'),
            'email' => $socialUser->getEmail(),
            'password' => null,
            'auth_provider' => $provider,
            'auth_provider_id' => $socialUser->getId(),
        ]);

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return $this->redirectAfterRegister($user, $role);
    }

    private function redirectAfterRegister(User $user, string $role): RedirectResponse
    {
        if ($role === SocialAuth::ROLE_MANAGER) {
            if ($user->hasRole('manager') && $user->restaurant_id) {
                return redirect()->route('manager.onboarding.waiting');
            }

            return redirect()->route('restaurant.oauth.complete');
        }

        if ($user->hasRole('waiter') && $user->global_waiter_number) {
            return redirect()->route('waiter.dashboard');
        }

        return redirect()->route('waiter.oauth.complete');
    }

    private function syncSocialProfile(User $user, $socialUser, string $provider): void
    {
        $user->forceFill([
            'auth_provider' => $provider,
            'auth_provider_id' => $socialUser->getId(),
            'name' => $user->name ?: ($socialUser->getName() ?: Str::before($socialUser->getEmail(), '@')),
        ])->save();
    }

    private function redirectBackForContext(string $role, string $intent): RedirectResponse
    {
        if ($intent === SocialAuth::INTENT_LOGIN) {
            return redirect()->route('login');
        }

        return redirect()->route($role === SocialAuth::ROLE_WAITER ? 'waiter.register' : 'restaurant.register');
    }
}
