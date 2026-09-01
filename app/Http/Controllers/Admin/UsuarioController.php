<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppRole;
use App\Enums\CapabilityType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserCapability;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $usuarios = User::query()->with('capabilities')->orderBy('apellidos')->orderBy('nombres')->get();

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'roles' => AppRole::cases(),
            'capabilities' => CapabilityType::cases(),
            'puedeCrear' => auth()->user()->rol === AppRole::Superusuario,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'nombres' => 'required|string|max:120',
            'apellidos' => 'required|string|max:120',
            'rol' => 'required|string',
        ]);

        $user = User::create([
            'email' => strtolower($validated['email']),
            'password' => $validated['password'],
            'nombres' => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'rol' => $validated['rol'],
            'activo' => true,
            'email_verified_at' => now(),
            'force_password_change' => true,
        ]);

        $this->audit->log('INSERT', $user);

        return back()->with('success', "Usuario {$user->email} creado. Debe cambiar la contraseña en el primer acceso.");
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

    public function delegate(Request $request, User $usuario): RedirectResponse
    {
        $this->authorize('delegate', $usuario);

        $validated = $request->validate([
            'capability' => 'required|string',
        ]);

        UserCapability::query()->updateOrCreate(
            [
                'user_id' => $usuario->id,
                'capability' => $validated['capability'],
            ],
            [
                'otorgado_por' => auth()->id(),
                'created_at' => now(),
            ]
        );

        return back()->with('success', 'Capacidad delegada correctamente.');
    }
}
