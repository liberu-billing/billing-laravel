<?php

declare(strict_types=1);

namespace App\Actions\Socialstream;

use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GenerateRedirectForProvider implements GeneratesProviderRedirect
{
    public function generate(string $provider): RedirectResponse
    {
        $driver = app(Socialite::class)->driver($provider);

        if (Socialstream::hasRememberSessionFeature()) {
            session()->put(
                'socialstream.previous_url',
                url()->previous()
            );
        }

        return $driver->redirect();
    }
}
