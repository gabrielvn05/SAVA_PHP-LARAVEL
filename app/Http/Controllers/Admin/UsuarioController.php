<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $usuarios = User::query()->orderBy('apellidos')->orderBy('nombres')->get();

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'roles' => AppRole::cases(),
        ]);
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $this->authorize('update', $usuario);

        $validated = $request->validate([
            'rol' => 'required|string',
            'activo' => 'required|boolean',
            'cedula' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'carrera' => 'nullable|string|max:120',
            'jornada' => 'nullable|string|max:60',
        ]);

        $usuario->update($validated);

        return back()->with('success', 'Usuario actualizado.');
    }
}
