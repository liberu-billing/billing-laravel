<?php

declare(strict_types=1);

namespace App\Actions\Socialstream;

use App\Models\User;
use Illuminate\Support\Carbon;
use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;
use Laravel\Socialite\Contracts\User as ProviderUser;

class UpdateConnectedAccount implements UpdatesConnectedAccounts
{
    public function update(User $user, ConnectedAccount $account, string $provider, ProviderUser $providerUser): ConnectedAccount
    {
        $account->forceFill([
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
        ])->save();

        return $account;
    }
}
