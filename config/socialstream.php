<?php

declare(strict_types=1);

use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\Providers;

return [

    /*
    |--------------------------------------------------------------------------
    | Auth Guard
    |--------------------------------------------------------------------------
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | OAuth Prompt
    |--------------------------------------------------------------------------
    */

    'prompt' => 'Or Login Via',

    /*
    |--------------------------------------------------------------------------
    | Socialstream Providers
    |--------------------------------------------------------------------------
    |
    | Twitter OAuth 1.0 is excluded — OAuth 1.0 requires live API keys even
    | for redirect and cannot be tested without real credentials.
    |
    */

    'providers' => [
        Providers::bitbucket(),
        Providers::facebook(),
        Providers::github(),
        Providers::gitlab(),
        Providers::google(),
        Providers::linkedin(),
        Providers::linkedinOpenId(),
        Providers::slack(),
        Providers::twitterOAuth2(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    */

    'features' => [
        Features::generateMissingEmails(),
        Features::createAccountOnFirstLogin(),
        Features::globalLogin(),
        Features::authExistingUnlinkedUsers(),
        Features::rememberSession(),
        Features::providerAvatars(),
        Features::refreshOAuthTokens(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Home Path
    |--------------------------------------------------------------------------
    */

    'home' => '/dashboard',

    /*
    |--------------------------------------------------------------------------
    | Redirect Configuration
    |--------------------------------------------------------------------------
    */

    'redirects' => [
        'login' => '/dashboard',
        'register' => '/dashboard',
        'login-failed' => '/login',
        'registration-failed' => '/register',
        'provider-linked' => '/user/profile',
        'provider-link-failed' => '/user/profile',
    ],

];
