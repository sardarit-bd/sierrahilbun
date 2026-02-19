<?php

namespace App\Services\OAuth\Providers;

class FacebookOAuthProvider extends AbstractOAuthProvider
{
    public function getProviderName(): string
    {
        return 'facebook';
    }

    protected function getScopes(): array
    {
        return ['email', 'public_profile'];
    }
}