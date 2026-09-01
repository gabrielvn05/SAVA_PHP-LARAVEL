<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordChangeController extends Controller
{
    public function edit(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user->force_password_change) {
            return redirect()->route('dashboard');
        }

        return view('auth.cambiar-clave');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->force_password_change) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => $validated['new_password'],
            'force_password_change' => false,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Contraseña actualizada correctamente.');
    }
}
