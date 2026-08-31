@extends('layouts.app')

@section('title', 'Proceso de aprobación')

@section('content')
<section class="stack">
    <header class="page-header">
        <h1>Proceso de aprobación</h1>
        <p class="field-hint">Solicitudes pendientes de revisión o firma.</p>
    </header>

    @forelse($solicitudes as $solicitud)
        <article class="card stack">
            <div class="row" style="justify-content: space-between;">
                <div>
                    <h2 style="margin: 0;">{{ $solicitud->tipo->label() }} — {{ $solicitud->creador->nombreCompleto() }}</h2>
                    <p class="field-hint">
                        {{ $solicitud->fecha_inicio->format('d/m/Y') }} – {{ $solicitud->fecha_fin->format('d/m/Y') }}
                        · <span class="badge {{ $solicitud->estado->badgeClass() }}">{{ $solicitud->estado->label() }}</span>
                    </p>
                    <p>{{ $solicitud->motivo }}</p>
                </div>
                <a href="{{ route('solicitudes.show', $solicitud) }}" class="btn btn--secondary btn--sm">Detalle</a>
            </div>

            @can('revisar', $solicitud)
                <form method="POST" action="{{ route('solicitudes.revisar', $solicitud) }}" class="stack">
                    @csrf
                    <label class="field">
                        <span class="field__label">Observaciones Secretaría</span>
                        <textarea name="observaciones_secretaria" rows="2" class="field__input"></textarea>
                    </label>
                    <div class="row">
                        <button type="submit" name="aprobado" value="1" class="btn btn--primary btn--sm">Aprobar revisión</button>
                        <button type="submit" name="aprobado" value="0" class="btn btn--danger btn--sm">Rechazar</button>
                    </div>
                </form>
            @endcan

            @can('aprobar', $solicitud)
                <form method="POST" action="{{ route('solicitudes.aprobar', $solicitud) }}" class="stack">
                    @csrf
                    <label class="field">
                        <span class="field__label">Observaciones Decano</span>
                        <textarea name="observaciones_decano" rows="2" class="field__input"></textarea>
                    </label>
                    <div class="row">
                        <button type="submit" name="aprobado" value="1" class="btn btn--primary btn--sm">Aprobar y firmar</button>
                        <button type="submit" name="aprobado" value="0" class="btn btn--danger btn--sm">Rechazar</button>
                    </div>
                </form>
            @endcan
        </article>
    @empty
        <article class="card">
            <p class="field-hint">No hay solicitudes pendientes.</p>
        </article>
    @endforelse
</section>
@endsection
