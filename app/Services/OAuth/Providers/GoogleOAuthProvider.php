<?php

namespace App\Services\OAuth\Providers;

class GoogleOAuthProvider extends AbstractOAuthProvider
{
    public function getProviderName(): string
    {
        return 'google';
    }

    protected function getScopes(): array
    {
        return ['email', 'profile'];
    }
}