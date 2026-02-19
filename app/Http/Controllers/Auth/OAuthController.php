<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OAuth\OAuthManager;
use App\Services\OAuth\OAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Throwable;

class OAuthController extends Controller
{
    public function __construct(
        private readonly OAuthManager $manager,
        private readonly OAuthService $oAuthService,
    ) {
    }

    /**
     * Redirect the user to the provider's OAuth page.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->ensureProviderIsSupported($provider);

        return $this->manager->driver($provider)->redirect();
    }

    /**
     * Handle the callback from the provider.
     */
    public function callback(string $provider): RedirectResponse
    {
        $this->ensureProviderIsSupported($provider);

        try {
            $socialiteUser = $this->manager->driver($provider)->handleCallback();
            $user = $this->oAuthService->findOrCreateUser($socialiteUser, $provider);

            auth()->login($user, remember: true);

            return redirect()->intended(route('dashboard'));

        } catch (Throwable $e) {
            report($e);
            return redirect()->route('login')
                ->withErrors(['oauth' => 'Authentication failed. Please try again.']);
        }
    }

    /**
     * Validate the provider before doing anything.
     */
    private function ensureProviderIsSupported(string $provider): void
    {
        if (!in_array($provider, $this->manager->getSupportedProviders())) {
            abort(404, "OAuth provider [{$provider}] is not supported.");
        }
    }
}