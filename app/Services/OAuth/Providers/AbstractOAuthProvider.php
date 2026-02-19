<?php

namespace App\Services\OAuth\Providers;

use App\Contracts\OAuthProviderInterface;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractOAuthProvider implements OAuthProviderInterface
{
    public function __construct(protected Socialite $socialite)
    {
    }

    /**
     * Redirect to the provider's OAuth page.
     */
    public function redirect(): RedirectResponse
    {
        return $this->socialite
            ->driver($this->getProviderName())
            ->scopes($this->getScopes())
            ->redirect();
    }

    /**
     * Handle callback and return the provider user.
     */
    public function handleCallback(): SocialiteUser
    {
        return $this->socialite
            ->driver($this->getProviderName())
            ->user();
    }

    /**
     * Default scopes — child classes can override to request more.
     */
    protected function getScopes(): array
    {
        return [];
    }
}