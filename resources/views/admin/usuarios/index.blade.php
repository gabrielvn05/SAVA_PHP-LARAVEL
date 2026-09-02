@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<section class="stack">
    <header class="page-header">
        <div class="page-header__text">
            <h1 class="page-header__title">Gestión de usuarios</h1>
        </div>
    </header>

    @if($puedeCrear)
        <article class="card stack">
            <h2 style="margin: 0; font-size: 1.1rem;">Crear usuario interno</h2>
            <form method="POST" action="{{ route('admin.usuarios.store') }}" class="form-grid">
                @csrf
                <label class="field">
                    <span class="field__label">Correo *</span>
                    <input type="email" name="email" required class="field__input" value="{{ old('email') }}">
                </label>
                <label class="field">
                    <span class="field__label">Contraseña *</span>
                    <input type="password" name="password" required minlength="8" class="field__input">
                </label>
                <label class="field">
                    <span class="field__label">Nombres *</span>
                    <input type="text" name="nombres" required class="field__input" value="{{ old('nombres') }}">
                </label>
                <label class="field">
                    <span class="field__label">Apellidos *</span>
                    <input type="text" name="apellidos" required class="field__input" value="{{ old('apellidos') }}">
                </label>
                <label class="field">
                    <span class="field__label">Rol *</span>
                    <select name="rol" required class="field__input">
                        @foreach($roles as $rol)
                            <option value="{{ $rol->value }}">{{ $rol->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="field field--full">
                    <button type="submit" class="btn btn--primary">Crear usuario</button>
                </div>
            </form>
        </article>
    @endif

    <article class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Activo</th>
                        <th>Capacidades extra</th>
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
                                @forelse($usuario->capabilities as $cap)
                                    <span class="badge badge--muted">{{ $cap->capability->label() }}</span>
                                @empty
                                    —
                                @endforelse
                            </td>
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

                                @if($puedeCrear)
                                    <form method="POST" action="{{ route('admin.usuarios.delegate', $usuario) }}" class="inline-form" style="margin-top: 0.5rem;">
                                        @csrf
                                        <select name="capability" class="field__input field__input--sm" required>
                                            @foreach($capabilities as $capability)
                                                <option value="{{ $capability->value }}">{{ $capability->label() }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn--secondary btn--sm">Delegar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
</section>
@endsection
