<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SocialstreamServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (! class_exists(\JoelButcher\Socialstream\Socialstream::class)) {
            return;
        }

        \JoelButcher\Socialstream\Socialstream::resolvesSocialiteUsersUsing(
            \App\Actions\Socialstream\ResolveSocialiteUser::class
        );
        \JoelButcher\Socialstream\Socialstream::createUsersFromProviderUsing(
            \App\Actions\Socialstream\CreateUserFromProvider::class
        );
        \JoelButcher\Socialstream\Socialstream::createConnectedAccountsUsing(
            \App\Actions\Socialstream\CreateConnectedAccount::class
        );
        \JoelButcher\Socialstream\Socialstream::updateConnectedAccountsUsing(
            \App\Actions\Socialstream\UpdateConnectedAccount::class
        );
        \JoelButcher\Socialstream\Socialstream::handlesInvalidStateUsing(
            \App\Actions\Socialstream\HandleInvalidState::class
        );
        \JoelButcher\Socialstream\Socialstream::generatesProvidersRedirectsUsing(
            \App\Actions\Socialstream\GenerateRedirectForProvider::class
        );
    }
}
