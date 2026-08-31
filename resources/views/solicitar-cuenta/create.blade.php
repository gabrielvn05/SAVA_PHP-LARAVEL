@extends('layouts.guest')

@section('title', 'Solicitar cuenta')

@php
    $mensajes = [
        'usuario_existe' => 'Este correo ya está registrado. Inicia sesión con Office 365.',
        'solicitud_pendiente' => 'Ya existe una solicitud pendiente para este correo.',
        'datos_incompletos' => 'Completa todos los campos obligatorios.',
        'cedula_invalida' => 'La cédula debe tener entre 10 y 13 dígitos.',
        'celular_invalido' => 'Indica un celular válido (mínimo 9 dígitos).',
        'carrera_invalida' => 'Selecciona una carrera válida.',
        'rol_invalido' => 'Selecciona un rol válido.',
        'correo_invalido' => 'Indica un correo electrónico válido.',
    ];
    $mensaje = $mensajes[$aviso ?? ''] ?? null;
@endphp

@section('content')
<div class="login-page">
    <aside class="login-hero">
        <span class="login-hero__badge">Acceso institucional</span>
        <h1>Solicitar cuenta</h1>
        <p>Si trabajas con la facultad y aún no tienes acceso, envía tu solicitud. El Decanato la revisará.</p>
    </aside>
    <div class="login-panel">
        <div class="card stack" style="width: 100%; max-width: 760px;">
            <div>
                <h2 style="margin: 0; font-size: 1.35rem;">Datos de la solicitud</h2>
                <p class="field-hint" style="margin: 0.4rem 0 0;">Usa tu correo institucional.</p>
            </div>

            <p class="field-hint">
                ¿Ya tienes cuenta? <a href="{{ route('login') }}" style="font-weight: 600;">Inicia sesión aquí</a>.
            </p>

            @if($mensaje)
                <div class="alert alert--error" role="alert">{{ $mensaje }}</div>
            @endif

            <form method="POST" action="{{ route('solicitar-cuenta.store') }}" class="stack">
                @csrf
                <div class="form-grid">
                    <label class="field">
                        <span class="field__label">Nombres *</span>
                        <input type="text" name="nombres" required class="field__input" value="{{ old('nombres') }}">
                    </label>
                    <label class="field">
                        <span class="field__label">Apellidos *</span>
                        <input type="text" name="apellidos" required class="field__input" value="{{ old('apellidos') }}">
                    </label>
                    <label class="field">
                        <span class="field__label">Cédula *</span>
                        <input type="text" name="cedula" required class="field__input" value="{{ old('cedula') }}">
                    </label>
                    <label class="field">
                        <span class="field__label">Celular *</span>
                        <input type="text" name="celular" required class="field__input" value="{{ old('celular') }}">
                    </label>
                    <label class="field field--full">
                        <span class="field__label">Correo institucional *</span>
                        <input type="email" name="email" required class="field__input" value="{{ old('email') }}">
                    </label>
                    <label class="field">
                        <span class="field__label">Carrera *</span>
                        <select name="carrera" required class="field__input">
                            <option value="">Seleccionar…</option>
                            @foreach($carreras as $carrera)
                                <option value="{{ $carrera['value'] }}" @selected(old('carrera') === $carrera['value'])>
                                    {{ $carrera['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field">
                        <span class="field__label">Rol solicitado *</span>
                        <select name="rol_solicitado" required class="field__input">
                            @foreach($roles as $rol)
                                <option value="{{ $rol->value }}" @selected(old('rol_solicitado', 'administrativo') === $rol->value)>
                                    {{ $rol->label() }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <button type="submit" class="btn btn--primary">Enviar solicitud</button>
            </form>
        </div>
    </div>
</div>
@endsection
