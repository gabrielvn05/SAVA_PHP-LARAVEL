@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<section class="stack">
    <header class="page-header">
        <h1>Gestión de usuarios</h1>
    </header>

    <article class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->nombreCompleto() }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td>{{ $usuario->rol->label() }}</td>
                            <td>{{ $usuario->activo ? 'Sí' : 'No' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}" class="inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <select name="rol" class="field__input field__input--sm">
                                        @foreach($roles as $rol)
                                            <option value="{{ $rol->value }}" @selected($usuario->rol === $rol)>{{ $rol->label() }}</option>
                                        @endforeach
                                    </select>
                                    <select name="activo" class="field__input field__input--sm">
                                        <option value="1" @selected($usuario->activo)>Activo</option>
                                        <option value="0" @selected(! $usuario->activo)>Inactivo</option>
                                    </select>
                                    <button type="submit" class="btn btn--secondary btn--sm">Guardar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
</section>
@endsection
