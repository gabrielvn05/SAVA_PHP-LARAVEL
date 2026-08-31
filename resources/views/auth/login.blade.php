@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
<div class="login-page">
    <aside class="login-hero">
        <span class="login-hero__badge">Acceso institucional</span>
        <h1>Sistema de Asistencia y Validaciones Académicas (SAVA)</h1>
        <p>Gestión de permisos y justificaciones</p>
    </aside>
    <div class="login-panel">
        <div class="card stack login-card">
            <div>
                <h2 style="margin: 0; font-size: 1.35rem;">Iniciar sesión</h2>
                <p class="field-hint" style="margin: 0.4rem 0 0;">
                    Usa tu cuenta institucional de Office 365.
                </p>
            </div>

            @if(session('error'))
                <div class="alert alert--error" role="alert">{{ session('error') }}</div>
            @endif

            @if(request('solicitud') === 'ok')
                <div class="alert alert--warning" role="status">
                    Solicitud enviada. El Decano revisará tu pedido; cuando sea aprobado podrás iniciar sesión con Office 365.
                </div>
            @endif

            <a href="{{ route('auth.microsoft') }}" class="btn btn--microsoft">
                Iniciar sesión con Microsoft 365
            </a>

            <div class="login-signup-cta">
                <p class="login-signup-cta__label">¿No tienes cuenta todavía?</p>
                <a href="{{ route('solicitar-cuenta.create') }}" class="btn btn--secondary">
                    Solicitar cuenta
                </a>
                <p class="field-hint" style="margin: 0.75rem 0 0; font-size: 0.78rem;">
                    Solo personal autorizado. Tu solicitud será revisada por el Decanato.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
