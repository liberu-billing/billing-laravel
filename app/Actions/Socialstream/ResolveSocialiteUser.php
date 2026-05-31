<?php

declare(strict_types=1);

namespace App\Actions\Socialstream;

use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use Laravel\Socialite\Contracts\Factory as Socialite;

class ResolveSocialiteUser implements ResolvesSocialiteUsers
{
    public function resolve(string $provider): \Laravel\Socialite\Contracts\User
    {
        return app(Socialite::class)->driver($provider)->user();
    }
}
