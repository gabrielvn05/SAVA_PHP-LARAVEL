@extends('layouts.app')

@section('title', 'Solicitud')

@php
    $d = $solicitud->detalle ?? [];
    $fmtFecha = function (?string $iso): string {
        if (! $iso) {
            return 'Pendiente';
        }
        try {
            return \Carbon\Carbon::parse($iso)->format('Y-m-d H:i');
        } catch (\Throwable) {
            return $iso;
        }
    };
@endphp

@section('content')
<section class="stack">
    <header class="page-header">
        <div class="page-header__text">
            <h1 class="page-header__title">Solicitud {{ $solicitud->tipo->label() }}</h1>
            <p class="page-header__subtitle">
                <span class="badge {{ $solicitud->estado->badgeClass() }}">{{ $solicitud->estado->label() }}</span>
                @if(!empty($d['codigo_tramite']))
                    · Código: <strong>{{ $d['codigo_tramite'] }}</strong>
                @endif
            </p>
        </div>
        <div class="page-header__actions">
            @can('update', $solicitud)
                <a href="{{ route('solicitudes.edit', $solicitud) }}" class="btn btn--secondary btn--sm">Editar</a>
            @endcan
            <a href="{{ route('solicitudes.preview-oficio', $solicitud) }}" class="btn btn--secondary btn--sm" target="_blank" rel="noopener">Vista previa oficio</a>
        </div>
    </header>

    <article class="card stack">
        <dl class="detail-list">
            <div><dt>Solicitante</dt><dd>{{ $solicitud->creador->nombreCompleto() }} ({{ $solicitud->creador->email }})</dd></div>
            <div><dt>Periodo</dt><dd>{{ $solicitud->fecha_inicio->format('d/m/Y') }} – {{ $solicitud->fecha_fin->format('d/m/Y') }}</dd></div>
            @if(!empty($d['hora_inicio']))
                <div><dt>Horario</dt><dd>{{ $d['hora_inicio'] }} – {{ $d['hora_fin'] ?? '' }}</dd></div>
            @endif
            @if(!empty($d['institucion_medica']))
                <div><dt>Institución médica</dt><dd>{{ $d['institucion_medica'] }}</dd></div>
            @endif
            @if(!empty($d['medico_tratante']))
                <div><dt>Médico tratante</dt><dd>{{ $d['medico_tratante'] }}</dd></div>
            @endif
            @if(!empty($d['diagnostico']))
                <div><dt>Diagnóstico</dt><dd>{{ $d['diagnostico'] }}</dd></div>
            @endif
            @if(!empty($d['nombre_evento']))
                <div><dt>Evento</dt><dd>{{ $d['nombre_evento'] }}</dd></div>
            @endif
            @if(!empty($d['lugar_evento']))
                <div><dt>Lugar</dt><dd>{{ $d['lugar_evento'] }}</dd></div>
            @endif
            @if(!empty($d['destino']))
                <div><dt>Destino</dt><dd>{{ $d['destino'] }}</dd></div>
            @endif
            @if(!empty($d['tipo_calamidad']))
                <div><dt>Tipo de calamidad</dt><dd>{{ $d['tipo_calamidad'] }}</dd></div>
            @endif
            @if(!empty($d['nombre_familiar']))
                <div><dt>Familiar</dt><dd>{{ $d['nombre_familiar'] }} ({{ $d['parentesco'] ?? '' }})</dd></div>
            @endif
            @if(!empty($d['jornada']))
                <div><dt>Jornada</dt><dd>{{ $d['jornada'] }}</dd></div>
            @endif
            @if(!empty($d['tipo_marcacion_omitida']))
                <div><dt>Marcación</dt><dd>{{ $d['tipo_marcacion_omitida'] }}</dd></div>
            @endif
            <div><dt>Motivo</dt><dd>{{ $solicitud->motivo }}</dd></div>
            @if($solicitud->justificativo_nombre)
                <div>
                    <dt>Justificativo</dt>
                    <dd>
                        @if(!empty($justificativoUrl))
                            <a href="{{ $justificativoUrl }}" target="_blank" rel="noopener">{{ $solicitud->justificativo_nombre }}</a>
                        @else
                            {{ $solicitud->justificativo_nombre }}
                        @endif
                    </dd>
                </div>
            @endif
            @if(!empty($d['anexos']))
                <div><dt>Anexos</dt>
                    <dd>
                        <ul style="margin: 0; padding-left: 1.1rem;">
                            @foreach($d['anexos'] as $anexo)
                                <li>{{ $anexo['nombre'] ?? 'Documento' }}</li>
                            @endforeach
                        </ul>
                    </dd>
                </div>
            @endif
            @if($solicitud->observaciones_secretaria)
                <div><dt>Obs. Secretaría</dt><dd>{{ $solicitud->observaciones_secretaria }}</dd></div>
            @endif
            @if($solicitud->observaciones_decano)
                <div><dt>Obs. Decano</dt><dd>{{ $solicitud->observaciones_decano }}</dd></div>
            @endif
        </dl>
    </article>

    <article class="card stack">
        <h2 style="margin: 0; font-size: 1.1rem;">Historial del trámite</h2>
        <div class="tramite-timeline" role="list" aria-label="Fases del trámite">
            <p class="field-hint" style="margin: 0 0 0.75rem;">
                Flujo completo del trámite. La fase actual está resaltada.
            </p>
            @foreach($timeline as $index => $event)
                @php
                    $isLast = $index === count($timeline) - 1;
                    $lineClass = $event['status'] === 'rejected'
                        ? 'tramite-timeline__line tramite-timeline__line--rejected'
                        : (in_array($event['status'], ['completed', 'current'], true)
                            ? 'tramite-timeline__line tramite-timeline__line--done'
                            : 'tramite-timeline__line');
                    $itemClass = 'tramite-timeline__item tramite-timeline__item--'.$event['status'];
                    if ($isLast) {
                        $itemClass .= ' tramite-timeline__item--last';
                    }
                    if ($event['id'] === 'final' && $event['status'] === 'completed') {
                        $itemClass .= ' tramite-timeline__item--approved';
                    }
                @endphp
                <div role="listitem" class="{{ $itemClass }}">
                    <div class="tramite-timeline__track">
                        @unless($isLast)
                            <div class="{{ $lineClass }}" aria-hidden="true"></div>
                        @endunless
                        <span class="tramite-timeline__dot tramite-timeline__dot--{{ $event['status'] }}" aria-hidden="true"></span>
                    </div>
                    <div class="tramite-timeline__icon" aria-hidden="true">{{ $event['icon'] }}</div>
                    <div class="tramite-timeline__body">
                        <div class="tramite-timeline__label-row">
                            <p class="tramite-timeline__label">Estado trámite: {{ $event['label'] }}</p>
                            @if($event['status'] === 'current')
                                <span class="tramite-timeline__badge">Fase actual</span>
                            @endif
                            @if($event['status'] === 'pending')
                                <span class="tramite-timeline__badge tramite-timeline__badge--pending">Pendiente</span>
                            @endif
                            @if($event['id'] === 'final' && $event['status'] === 'completed')
                                <span class="tramite-timeline__badge tramite-timeline__badge--success">Aprobado</span>
                            @endif
                            @if($event['id'] === 'final' && $event['status'] === 'rejected')
                                <span class="tramite-timeline__badge tramite-timeline__badge--rejected">Rechazado</span>
                            @endif
                        </div>
                        <p class="tramite-timeline__fecha">Fecha: {{ $fmtFecha($event['fecha'] ?? null) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </article>
</section>
@endsection
