@extends('layouts.app')

@section('title', 'Panel')

@section('content')
<section class="stack">
    <header class="page-header">
        <h1>Hola, {{ $user->nombres }}</h1>
        <p class="field-hint">Panel de {{ $stats['scopeLabel'] }}: indicadores y accesos rápidos.</p>
    </header>

    <div class="dash-metrics">
        <article class="metric-card metric-card--primary">
            <span class="metric-card__label">Total solicitudes</span>
            <strong class="metric-card__value">{{ $stats['total'] }}</strong>
        </article>
        <article class="metric-card metric-card--warning">
            <span class="metric-card__label">En proceso</span>
            <strong class="metric-card__value">{{ $stats['enProceso'] }}</strong>
        </article>
        <article class="metric-card metric-card--success">
            <span class="metric-card__label">Aprobadas</span>
            <strong class="metric-card__value">{{ $stats['aprobadas'] }}</strong>
            <span class="metric-card__hint">Tasa: {{ $stats['tasaAprobacion'] }}%</span>
        </article>
        <article class="metric-card metric-card--info">
            <span class="metric-card__label">Últimos 30 días</span>
            <strong class="metric-card__value">{{ $stats['recientes30Dias'] }}</strong>
        </article>
    </div>

    <div class="dashboard-grid">
        <article class="card dashboard-tile stack">
            <h2 style="margin: 0;">Mis solicitudes</h2>
            <p class="field-hint">Consulta, crea y edita tus solicitudes.</p>
            <div class="row">
                <a href="{{ route('solicitudes.index') }}" class="btn btn--secondary btn--sm">Ver solicitudes</a>
                <a href="{{ route('solicitudes.create') }}" class="btn btn--primary btn--sm">Nueva solicitud</a>
            </div>
        </article>

        @if(auth()->user()->hasCapability(\App\Enums\CapabilityType::RevisarSolicitudes))
            <article class="card dashboard-tile stack">
                <h2 style="margin: 0;">Revisión (Secretaría)</h2>
                <a href="{{ route('solicitudes.proceso') }}" class="btn btn--secondary btn--sm">Abrir proceso</a>
            </article>
        @endif

        @if(auth()->user()->hasCapability(\App\Enums\CapabilityType::AprobarSolicitudes))
            <article class="card dashboard-tile stack">
                <h2 style="margin: 0;">Aprobación y firma</h2>
                <a href="{{ route('solicitudes.proceso') }}" class="btn btn--secondary btn--sm">Atender pendientes</a>
            </article>
        @endif

        @if(isset($stats['solicitudesCuentaPendientes']))
            <article class="card dashboard-tile stack">
                <h2 style="margin: 0;">Solicitudes de cuenta</h2>
                <p class="field-hint">Pendientes: {{ $stats['solicitudesCuentaPendientes'] }}</p>
                <a href="{{ route('admin.solicitudes-cuenta.index') }}" class="btn btn--primary btn--sm">Revisar</a>
            </article>
        @endif

        <article class="card dashboard-tile stack">
            <h2 style="margin: 0;">Rechazadas</h2>
            <p class="field-hint">Total: {{ $stats['rechazadas'] }}</p>
            <a href="{{ route('solicitudes.index') }}" class="btn btn--secondary btn--sm">Ver historial</a>
        </article>
    </div>
</section>
@endsection
