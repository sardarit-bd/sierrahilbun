<?php

namespace App\Providers;

use App\Services\OAuth\OAuthManager;
use App\Services\OAuth\Providers\FacebookOAuthProvider;
use App\Services\OAuth\Providers\GoogleOAuthProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory as Socialite;

class OAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OAuthManager::class, function ($app) {
            $socialite = $app->make(Socialite::class);

            return new OAuthManager([
                'google'   => new GoogleOAuthProvider($socialite),
                'facebook' => new FacebookOAuthProvider($socialite),
            ]);
        });
    }
}