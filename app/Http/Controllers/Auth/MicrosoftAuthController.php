<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AppRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class MicrosoftAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('azure')
            ->scopes(['openid', 'profile', 'email', 'User.Read'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        $azureUser = Socialite::driver('azure')->user();

        $email = strtolower($azureUser->getEmail() ?? '');

        if ($email === '') {
            return redirect()->route('login')
                ->with('error', 'No se pudo obtener el correo institucional de Office 365.');
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'No tienes una cuenta registrada en SAVA. Solicita acceso al Decano.');
        }

        if (! $user->activo) {
            return redirect()->route('login')
                ->with('error', 'Tu cuenta está inactiva. Contacta al Decano.');
        }

        $user->update([
            'microsoft_id' => $azureUser->getId(),
            'email_verified_at' => now(),
            'nombres' => $this->extractFirstName($azureUser),
            'apellidos' => $this->extractLastName($azureUser),
        ]);

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function extractFirstName(object $azureUser): string
    {
        $name = trim((string) ($azureUser->user['givenName'] ?? $azureUser->getName() ?? ''));
        if ($name !== '') {
            return Str::before($name, ' ') ?: $name;
        }

        return Str::before($azureUser->getEmail(), '@');
    }

    private function extractLastName(object $azureUser): string
    {
        $surname = trim((string) ($azureUser->user['surname'] ?? ''));
        if ($surname !== '') {
            return $surname;
        }

        $full = trim((string) $azureUser->getName());
        if ($full !== '') {
            return Str::after($full, ' ') ?: 'Pendiente';
        }

        return 'Pendiente';
    }
}
