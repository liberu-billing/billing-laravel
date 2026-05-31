<?php

declare(strict_types=1);

namespace App\Actions\Socialstream;

use App\Models\ConnectedAccount;
use App\Models\User;
use Illuminate\Support\Carbon;
use JoelButcher\Socialstream\ConnectedAccount as SocialstreamConnectedAccount;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use Laravel\Socialite\Contracts\User as ProviderUser;

class CreateConnectedAccount implements CreatesConnectedAccounts
{
    public function create(User $user, string $provider, ProviderUser $providerUser): SocialstreamConnectedAccount
    {
        return $user->connectedAccounts()->create([
            'provider' => $provider,
            'provider_id' => $providerUser->getId(),
            'name' => $providerUser->getName(),
            'nickname' => $providerUser->getNickname(),
            'email' => $providerUser->getEmail(),
            'avatar_path' => $providerUser->getAvatar(),
            'token' => $providerUser->token,
            'secret' => $providerUser->tokenSecret ?? null,
            'refresh_token' => $providerUser->refreshToken ?? null,
            'expires_at' => isset($providerUser->expiresIn)
                ? Carbon::now()->addSeconds($providerUser->expiresIn)
                : null,
        ]);
    }
}
