

<?php

namespace App\Http\Controllers;

use App\Services\IntegrationService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class IntegrationController extends Controller
{
    public function __construct(
        protected IntegrationService $integrationService
    ) {}

    public function redirect(Request $request, string $provider)
    {
        return Socialite::driver($provider)
            ->scopes(config("services.{$provider}.scopes", []))
            ->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        $socialiteUser = Socialite::driver($provider)->user();
        
        $this->integrationService->connect($provider, $request->user(), [
            'token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken,
            'expires_in' => $socialiteUser->expiresIn,
        ]);

        return redirect()->route('integrations.index')
            ->with('status', 'Integration connected successfully.');
    }

    public function destroy(Request $request, string $provider)
    {
        $this->integrationService->disconnect($provider, $request->user());

        return back()->with('status', 'Integration disconnected successfully.');
    }
}