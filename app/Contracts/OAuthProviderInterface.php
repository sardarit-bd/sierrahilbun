<?php

namespace App\Contracts;

use Laravel\Socialite\Contracts\User as SocialiteUser;

interface OAuthProviderInterface
{
    /**
     * Redirect the user to the OAuth provider.
     */
    public function redirect(): \Symfony\Component\HttpFoundation\RedirectResponse;

    /**
     * Handle the callback and return the authenticated provider user.
     */
    public function handleCallback(): SocialiteUser;

    /**
     * Return the unique driver name (e.g. 'google', 'facebook').
     */
    public function getProviderName(): string;
}