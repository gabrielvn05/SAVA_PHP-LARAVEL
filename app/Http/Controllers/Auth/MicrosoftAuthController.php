<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MicrosoftAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class MicrosoftAuthController extends Controller
{
    public function __construct(private readonly MicrosoftAccountService $accounts) {}

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('azure')
            ->scopes(['openid', 'profile', 'email', 'User.Read'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $azureUser = Socialite::driver('azure')->user();
        } catch (Throwable $e) {
            Log::warning('Fallo OAuth Microsoft 365', ['error' => $e->getMessage()]);

            return redirect()->route('login')
                ->with('error', 'No se pudo completar el inicio de sesión con Microsoft 365. Intenta de nuevo.');
        }

        $result = $this->accounts->findOrCreateFromAzure($azureUser);

        if (! $result['ok']) {
            return redirect()->route('login')->with('error', $result['error']);
        }

        Auth::login($result['user'], remember: true);

        if ($result['user']->needsProfileCompletion()) {
            return redirect()->route('perfil.completar');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(): RedirectResponse
    {
        $cerrarSesionMicrosoft = filled(Auth::user()?->microsoft_id);

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        if ($cerrarSesionMicrosoft) {
            $tenant = config('services.azure.tenant') ?: 'common';
            $logout = 'https://login.microsoftonline.com/'.$tenant.'/oauth2/v2.0/logout';

            return redirect()->away($logout.'?'.http_build_query([
                'post_logout_redirect_uri' => route('login'),
            ]));
        }

        return redirect()->route('login');
    }
}
