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
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $usuarios = User::query()->with('capabilities')->orderBy('apellidos')->orderBy('nombres')->get();

        $actor = auth()->user();
        $rolesAsignables = $this->rolesAsignables($actor);

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'roles' => $rolesAsignables,
            'capabilities' => CapabilityType::cases(),
            'puedeCrear' => $actor->rol === AppRole::Superusuario,
            'puedeCambiarRol' => $actor->hasCapability(CapabilityType::GestionarUsuarios),
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

        $actor = auth()->user();
        $rolesAsignables = $this->rolesAsignables($actor);

        if ($actor->rol === AppRole::Decano && $usuario->rol === AppRole::Superusuario) {
            return back()->with('error', 'El Decano no puede modificar una cuenta de Superusuario.');
        }

        $validated = $request->validate([
            'rol' => ['required', 'string', Rule::in(array_map(static fn (AppRole $rol) => $rol->value, $rolesAsignables))],
            'activo' => 'required|boolean',
        ]);

        $nuevoRol = AppRole::from($validated['rol']);

        if (
            $usuario->rol === AppRole::Superusuario
            && $nuevoRol !== AppRole::Superusuario
            && User::query()->where('rol', AppRole::Superusuario)->count() <= 1
        ) {
            return back()->with('error', 'Debe quedar al menos un Superusuario.');
        }

        $old = $usuario->toArray();
        $usuario->update([
            'rol' => $nuevoRol,
            'activo' => $request->boolean('activo'),
        ]);
        $this->audit->log('UPDATE', $usuario, $old);

        return back()->with('success', 'Rol actualizado: '.$usuario->nombreCompleto().' ahora es '.$nuevoRol->label().'.');
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

    /** @return list<AppRole> */
    private function rolesAsignables(User $actor): array
    {
        $roles = AppRole::cases();

        if ($actor->rol === AppRole::Superusuario) {
            return $roles;
        }

        return array_values(array_filter(
            $roles,
            fn (AppRole $rol): bool => $rol !== AppRole::Superusuario,
        ));
    }
}
