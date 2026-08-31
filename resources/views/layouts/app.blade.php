<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SAVA') — Sistema de Asistencia y Validaciones Académicas</title>
    @vite(['resources/css/app.css'])
</head>
<body class="app-shell">
    <header class="topbar app-shell__header">
        <div class="topbar__start">
            <div class="topbar__brand">
                <div class="topbar__titles">
                    <span class="topbar__name">SAVA</span>
                    <span class="topbar__tagline">Sistema de Asistencia y Validaciones Académicas</span>
                </div>
            </div>
        </div>
        <nav class="topbar__nav">
            <a href="{{ route('dashboard') }}" class="topbar__link">Panel</a>
            <a href="{{ route('solicitudes.index') }}" class="topbar__link">Solicitudes</a>
            @if(auth()->user()->hasCapability(\App\Enums\CapabilityType::RevisarSolicitudes) || auth()->user()->hasCapability(\App\Enums\CapabilityType::AprobarSolicitudes))
                <a href="{{ route('solicitudes.proceso') }}" class="topbar__link">Proceso</a>
            @endif
            @if(auth()->user()->hasCapability(\App\Enums\CapabilityType::GestionarUsuarios))
                <a href="{{ route('admin.usuarios.index') }}" class="topbar__link">Usuarios</a>
            @endif
            @if(auth()->user()->can('viewAny', \App\Models\AccountRequest::class))
                <a href="{{ route('admin.solicitudes-cuenta.index') }}" class="topbar__link">Cuentas</a>
            @endif
        </nav>
        <div class="topbar__user">
            <div class="topbar__user-meta">
                <span class="topbar__user-name">{{ auth()->user()->nombreCompleto() }}</span>
                <span class="topbar__user-email">{{ auth()->user()->email }}</span>
                <span class="topbar__pill">{{ auth()->user()->rol->label() }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn--ghost btn--sm">Salir</button>
            </form>
        </div>
    </header>

    <div class="app-shell-layout">
        <main class="app-main">
            @if(session('success'))
                <div class="alert alert--success" role="status">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert--error" role="alert">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
