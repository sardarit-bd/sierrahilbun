<?php

namespace App\Services\OAuth;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class OAuthService
{
    /**
     * Find or create a user from the OAuth provider's user data.
     */
    public function findOrCreateUser(SocialiteUser $socialiteUser, string $provider): User
    {
        $socialAccount = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', $socialiteUser->getId())
            ->first();

        if ($socialAccount) {
            $this->updateAvatar($socialAccount, $socialiteUser->getAvatar());
            return $socialAccount->user;
        }

        return $this->createUserWithSocialAccount($socialiteUser, $provider);
    }

    /**
     * Create a new user and link the social account.
     * Wrapped in a transaction to ensure data integrity.
     */
    private function createUserWithSocialAccount(SocialiteUser $socialiteUser, string $provider): User
    {
        return DB::transaction(function () use ($socialiteUser, $provider) {
            $user = User::firstOrCreate(
                ['email' => $socialiteUser->getEmail()],
                [
                    'name'              => $socialiteUser->getName(),
                    'email_verified_at' => now(), // OAuth emails are already verified
                    'password'          => null,   // No password for OAuth users
                ]
            );

            $user->socialAccounts()->create([
                'provider'    => $provider,
                'provider_id' => $socialiteUser->getId(),
                'avatar'      => $socialiteUser->getAvatar(),
            ]);

            return $user;
        });
    }

    /**
     * Keep the avatar in sync on every login.
     */
    private function updateAvatar(SocialAccount $socialAccount, ?string $avatar): void
    {
        if ($avatar && $socialAccount->avatar !== $avatar) {
            $socialAccount->update(['avatar' => $avatar]);
        }
    }
}