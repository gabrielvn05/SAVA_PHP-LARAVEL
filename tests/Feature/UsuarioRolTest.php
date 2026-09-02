<?php

namespace Tests\Feature;

use App\Enums\AppRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioRolTest extends TestCase
{
    use RefreshDatabase;

    public function test_decano_can_open_users_and_change_a_role(): void
    {
        $decano = $this->usuario(AppRole::Decano, '1313000001');
        $docente = $this->usuario(AppRole::Docente, '1313000002');

        $this->actingAs($decano)
            ->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertSee('Cambiar rol')
            ->assertSee($docente->email)
            ->assertDontSee('superusuario', false);

        $this->actingAs($decano)
            ->from(route('admin.usuarios.index'))
            ->patch(route('admin.usuarios.update', $docente), [
                'rol' => AppRole::Secretaria->value,
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.usuarios.index'))
            ->assertSessionHas('success');

        $this->assertSame(AppRole::Secretaria, $docente->fresh()->rol);
    }

    public function test_superusuario_can_change_a_role(): void
    {
        $super = $this->usuario(AppRole::Superusuario, '1313000003');
        $admin = $this->usuario(AppRole::Administrativo, '1313000004');

        $this->actingAs($super)
            ->patch(route('admin.usuarios.update', $admin), [
                'rol' => AppRole::Decano->value,
                'activo' => '1',
            ])
            ->assertRedirect();

        $this->assertSame(AppRole::Decano, $admin->fresh()->rol);
    }

    public function test_decano_cannot_assign_superusuario(): void
    {
        $decano = $this->usuario(AppRole::Decano, '1313000005');
        $docente = $this->usuario(AppRole::Docente, '1313000006');

        $this->actingAs($decano)
            ->patch(route('admin.usuarios.update', $docente), [
                'rol' => AppRole::Superusuario->value,
                'activo' => '1',
            ])
            ->assertSessionHasErrors('rol');

        $this->assertSame(AppRole::Docente, $docente->fresh()->rol);
    }

    public function test_secretaria_cannot_manage_users(): void
    {
        $secretaria = $this->usuario(AppRole::Secretaria, '1313000007');

        $this->actingAs($secretaria)
            ->get(route('admin.usuarios.index'))
            ->assertForbidden();
    }

    private function usuario(AppRole $rol, string $cedula): User
    {
        return User::factory()->create([
            'rol' => $rol,
            'cedula' => $cedula,
            'carrera' => 'software',
            'celular' => '0991234567',
        ]);
    }
}
