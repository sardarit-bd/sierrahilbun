<?php

namespace App\Services\OAuth;

use App\Contracts\OAuthProviderInterface;
use InvalidArgumentException;

class OAuthManager
{
    /**
     * @param array<string, OAuthProviderInterface> $providers
     */
    public function __construct(
        private readonly array $providers = []
    ) {
    }

    /**
     * Resolve the correct provider by name.
     */
    public function driver(string $provider): OAuthProviderInterface
    {
        if (!array_key_exists($provider, $this->providers)) {
            throw new InvalidArgumentException("OAuth provider [{$provider}] is not supported.");
        }

        return $this->providers[$provider];
    }

    /**
     * Get all registered provider names.
     */
    public function getSupportedProviders(): array
    {
        return array_keys($this->providers);
    }
}