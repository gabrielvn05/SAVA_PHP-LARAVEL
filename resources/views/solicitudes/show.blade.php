@extends('layouts.app')

@section('title', 'Solicitud')

@section('content')
<section class="stack">
    <header class="page-header row" style="justify-content: space-between; align-items: flex-end;">
        <div>
            <h1>Solicitud {{ $solicitud->tipo->label() }}</h1>
            <p class="field-hint">
                <span class="badge {{ $solicitud->estado->badgeClass() }}">{{ $solicitud->estado->label() }}</span>
            </p>
        </div>
        @can('update', $solicitud)
            <a href="{{ route('solicitudes.edit', $solicitud) }}" class="btn btn--secondary btn--sm">Editar</a>
        @endcan
    </header>

    <article class="card stack">
        <dl class="detail-list">
            <div><dt>Solicitante</dt><dd>{{ $solicitud->creador->nombreCompleto() }}</dd></div>
            <div><dt>Periodo</dt><dd>{{ $solicitud->fecha_inicio->format('d/m/Y') }} – {{ $solicitud->fecha_fin->format('d/m/Y') }}</dd></div>
            <div><dt>Motivo</dt><dd>{{ $solicitud->motivo }}</dd></div>
            @if($solicitud->justificativo_nombre)
                <div><dt>Justificativo</dt><dd>{{ $solicitud->justificativo_nombre }}</dd></div>
            @endif
            @if($solicitud->observaciones_secretaria)
                <div><dt>Obs. Secretaría</dt><dd>{{ $solicitud->observaciones_secretaria }}</dd></div>
            @endif
            @if($solicitud->observaciones_decano)
                <div><dt>Obs. Decano</dt><dd>{{ $solicitud->observaciones_decano }}</dd></div>
            @endif
        </dl>
    </article>
</section>
@endsection
