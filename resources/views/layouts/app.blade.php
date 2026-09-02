<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0c3d7a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="SAVA">
    <title>@yield('title', 'SAVA') — Sistema de Asistencia y Validaciones Académicas</title>
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" href="{{ asset('branding/icon-192.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('branding/icon-192.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $usuario = auth()->user();
    $rol = $usuario->rol;
    $esSuper = $rol === \App\Enums\AppRole::Superusuario;
    $esDecano = $rol === \App\Enums\AppRole::Decano;
    $esSecretaria = $rol === \App\Enums\AppRole::Secretaria;
    $puedeProceso = $esSecretaria || $esDecano;
    $solicitudesOpen = request()->routeIs('solicitudes.*')
        && ! request()->routeIs('solicitudes.proceso')
        && ! ($puedeProceso && request()->routeIs('solicitudes.show'));
    $mostrarPill = ! $esSuper;
@endphp
<body class="app-shell">
    <a class="skip-to-main" href="#contenido-principal">Saltar al contenido</a>

    <div class="app-shell-layout app-shell-layout--with-sidebar" data-app-shell>
        <header class="topbar topbar--light app-shell__header">
            <div class="topbar__start">
                <button
                    type="button"
                    class="sidebar-toggle"
                    data-sidebar-toggle
                    aria-label="Abrir menú"
                    aria-expanded="false"
                >
                    <span class="sidebar-toggle__bar"></span>
                    <span class="sidebar-toggle__bar"></span>
                    <span class="sidebar-toggle__bar"></span>
                </button>
                <div class="topbar__brand">
                    <img class="topbar__logo-img" src="{{ asset('branding/LOGO-ULEAM.png') }}" alt="Universidad Laica Eloy Alfaro de Manabí">
                    <div class="topbar__titles">
                        <span class="topbar__name">SAVA</span>
                        <span class="topbar__tagline">Permisos y justificaciones</span>
                    </div>
                </div>
            </div>
            <div class="topbar__user">
                <a href="{{ route('perfil.edit') }}" class="topbar__user-link" title="Perfil y configuración">
                    <div class="topbar__user-meta">
                        <span class="topbar__user-name">{{ $usuario->nombreCompleto() }}</span>
                        <span class="topbar__user-email">{{ $usuario->email }}</span>
                        @if($mostrarPill)
                            <span class="topbar__pill">{{ $usuario->rol->label() }}</span>
                        @endif
                    </div>
                </a>
            </div>
        </header>

        <div class="app-shell__workspace">
            <div class="sidebar-backdrop" data-sidebar-backdrop aria-hidden="true"></div>
            <aside class="sidebar-panel" data-sidebar-panel aria-label="Menú lateral">
                <nav class="sidebar-nav" aria-label="Menú principal">
                    <a href="{{ route('dashboard') }}" class="sidebar-nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Inicio</a>

                    @if($esSuper)
                        <a href="{{ route('admin.usuarios.index') }}" class="sidebar-nav__link {{ request()->routeIs('admin.usuarios.*') ? 'is-active' : '' }}">Usuarios</a>
                        <a href="{{ route('admin.solicitudes-cuenta.index') }}" class="sidebar-nav__link {{ request()->routeIs('admin.solicitudes-cuenta.*') ? 'is-active' : '' }}">Solicitudes de cuenta</a>
                    @else
                        <div class="sidebar-nav__group" data-nav-group>
                            <button type="button" class="sidebar-nav__group-btn" aria-expanded="{{ $solicitudesOpen ? 'true' : 'false' }}" data-nav-group-btn>
                                <span>Solicitudes</span>
                                <span class="sidebar-nav__chevron" aria-hidden style="transform: rotate({{ $solicitudesOpen ? '180deg' : '0deg' }});">▼</span>
                            </button>
                            <div class="sidebar-nav__sub" @unless($solicitudesOpen) hidden @endunless data-nav-group-sub>
                                <a href="{{ route('solicitudes.wizard') }}" class="sidebar-nav__sublink {{ request()->routeIs('solicitudes.wizard', 'solicitudes.create') ? 'is-active' : '' }}">Nuevas solicitudes</a>
                                <a href="{{ route('solicitudes.index') }}" class="sidebar-nav__sublink {{ request()->routeIs('solicitudes.index', 'solicitudes.show', 'solicitudes.edit') ? 'is-active' : '' }}">Mis solicitudes</a>
                            </div>
                        </div>

                        @if($puedeProceso)
                            <a href="{{ route('solicitudes.proceso') }}" class="sidebar-nav__link {{ request()->routeIs('solicitudes.proceso') || ($puedeProceso && request()->routeIs('solicitudes.show')) ? 'is-active' : '' }}">Proceso de aprobación</a>
                        @endif
                        @if($usuario->hasCapability(\App\Enums\CapabilityType::GestionarUsuarios))
                            <a href="{{ route('admin.usuarios.index') }}" class="sidebar-nav__link {{ request()->routeIs('admin.usuarios.*') ? 'is-active' : '' }}">Usuarios</a>
                        @endif
                        @if($esSecretaria || $esDecano)
                            <a href="{{ route('reportes.index') }}" class="sidebar-nav__link {{ request()->routeIs('reportes.*') ? 'is-active' : '' }}">Reportes</a>
                            <a href="{{ route('admin.solicitudes-cuenta.index') }}" class="sidebar-nav__link {{ request()->routeIs('admin.solicitudes-cuenta.*') ? 'is-active' : '' }}">Solicitudes de cuenta</a>
                        @endif
                    @endif
                </nav>

                <div class="sidebar-panel__footer">
                    <a href="{{ route('perfil.edit') }}" class="sidebar-panel__profile {{ request()->routeIs('perfil.*') ? 'is-active' : '' }}">Perfil y configuración</a>
                    <button class="sidebar-panel__logout" type="button" data-logout-open>Cerrar sesión</button>
                </div>
            </aside>

            <div class="app-shell__content page-enter">
                <div class="app-shell__body">
                    <main id="contenido-principal" class="app-main" tabindex="-1">
                        @if(session('success'))
                            <div class="alert alert--success" role="status">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert--error" role="alert">{{ session('error') }}</div>
                        @endif
                        @yield('content')
                    </main>
                </div>
            </div>
        </div>
    </div>

    @if(request()->routeIs('perfil.completar'))
        <div class="profile-modal" data-profile-modal role="dialog" aria-modal="true" aria-labelledby="profile-modal-title">
            <div class="profile-modal__backdrop" aria-hidden="true"></div>
            <div class="profile-modal__panel">
                <h2 id="profile-modal-title" class="profile-modal__title">Actualiza tus datos</h2>
                <p class="profile-modal__text">
                    Microsoft 365 no envía cédula ni carrera. Completa estos datos para continuar.
                    Luego podrás cambiarlos en <strong>Perfil y configuración</strong>.
                </p>
                @if($errors->any())
                    <div class="alert alert--error" role="alert">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('perfil.update') }}" class="stack" data-loading-label="Guardando datos…">
                    @csrf
                    @method('PUT')
                    @include('perfil._campos', ['user' => $usuario, 'carreras' => \App\Support\Carreras::OPCIONES])
                    <div class="profile-modal__actions">
                        <button type="button" class="btn btn--ghost" data-logout-open>Cerrar sesión</button>
                        <button type="submit" class="btn btn--primary">Guardar y continuar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="logout-modal" hidden data-logout-modal role="dialog" aria-modal="true" aria-labelledby="logout-modal-title">
        <button type="button" class="logout-modal__backdrop" aria-label="Cerrar" data-logout-close></button>
        <div class="logout-modal__panel">
            <h2 id="logout-modal-title" class="logout-modal__title">Cerrar sesión</h2>
            <p class="logout-modal__text">¿Seguro que desea cerrar sesión?</p>
            <div class="logout-modal__actions">
                <button type="button" class="btn btn--ghost" data-logout-close>Cancelar</button>
                <button type="submit" class="btn btn--primary" form="logout-form">Aceptar</button>
            </div>
        </div>
    </div>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" hidden>
        @csrf
    </form>
</body>
</html>
