<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = strtolower($validated['email']);
        $user = User::query()->where('email', $email)->first();

        if (
            ! $user
            || ! $user->password
            || ! Hash::check($validated['password'], $user->password)
        ) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Credenciales incorrectas o usuario sin contraseña asignada.',
                ]);
        }

        if (! $user->activo) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        Auth::login($user, $request->boolean('remember'));

        return redirect()->intended(route('dashboard'));
    }
}
