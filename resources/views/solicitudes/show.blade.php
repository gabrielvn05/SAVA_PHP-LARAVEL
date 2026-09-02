@extends('layouts.app')

@section('title', 'Solicitud')

@php
    $d = $solicitud->detalle ?? [];
    $fmtFecha = function (?string $iso): string {
        if (! $iso) {
            return 'Pendiente';
        }
        try {
            return \Carbon\Carbon::parse($iso)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return $iso;
        }
    };
    $estado = $solicitud->estado;
    $bannerClass = match ($estado) {
        \App\Enums\SolicitudEstado::Aprobada => 'solicitud-banner solicitud-banner--aprobada',
        \App\Enums\SolicitudEstado::Rechazada => 'solicitud-banner solicitud-banner--rechazada',
        default => 'solicitud-banner solicitud-banner--proceso',
    };
    $bannerTitulo = match ($estado) {
        \App\Enums\SolicitudEstado::Aprobada => 'Trámite aprobado',
        \App\Enums\SolicitudEstado::Rechazada => 'Trámite rechazado',
        \App\Enums\SolicitudEstado::EnBorrador => 'Borrador pendiente de envío',
        \App\Enums\SolicitudEstado::EnRevisionSecretaria => 'En revisión de Secretaría',
        \App\Enums\SolicitudEstado::PendienteAprobacionDecano => 'Pendiente de firma del Decano',
        default => $estado->label(),
    };
    $bannerTexto = match ($estado) {
        \App\Enums\SolicitudEstado::Aprobada => 'El trámite fue autorizado y queda cerrado.',
        \App\Enums\SolicitudEstado::Rechazada => 'El trámite no fue autorizado. Revisa las observaciones en esta ficha.',
        \App\Enums\SolicitudEstado::EnBorrador => 'Puedes editar los datos y enviarla cuando esté completa.',
        \App\Enums\SolicitudEstado::EnRevisionSecretaria => 'Secretaría revisará el justificativo y los datos del período.',
        \App\Enums\SolicitudEstado::PendienteAprobacionDecano => 'Secretaría ya revisó el trámite. Falta la resolución del Decano.',
        default => '',
    };
@endphp

@section('content')
<section class="solicitud-ficha">
    <header class="page-header">
        <div class="page-header__text">
            <p class="solicitud-kicker">Detalle del trámite</p>
            <h1 class="page-header__title">{{ $solicitud->tipo->label() }}</h1>
            <p class="page-header__subtitle">
                @if(!empty($d['codigo_tramite']))
                    Código <strong>{{ $d['codigo_tramite'] }}</strong>
                    ·
                @endif
                Registrada el {{ $solicitud->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="page-header__actions">
            @if($esStaff)
                <a href="{{ route('solicitudes.proceso') }}" class="btn btn--secondary btn--sm">Proceso de aprobación</a>
            @else
                <a href="{{ route('solicitudes.index') }}" class="btn btn--secondary btn--sm">Mis solicitudes</a>
            @endif
            @if(auth()->id() === $solicitud->creado_por)
                @can('update', $solicitud)
                    <a href="{{ route('solicitudes.edit', $solicitud) }}" class="btn btn--secondary btn--sm">Editar</a>
                @endcan
            @endif
        </div>
    </header>

    <div class="{{ $bannerClass }}" role="status">
        <span class="badge {{ $estado->badgeClass() }}">{{ $estado->label() }}</span>
        <div>
            <p class="solicitud-banner__title">{{ $bannerTitulo }}</p>
            <p class="solicitud-banner__text">{{ $bannerTexto }}</p>
        </div>
    </div>

    @if($puedeActuarSecretaria || $puedeActuarDecano)
        @php
            $accionUrl = $puedeActuarSecretaria
                ? route('solicitudes.revisar', $solicitud)
                : route('solicitudes.aprobar', $solicitud);
            $campoObservacion = $puedeActuarSecretaria ? 'observaciones_secretaria' : 'observaciones_decano';
        @endphp
        <article class="card stack" data-proceso-acciones>
            <h2 class="solicitud-section-title">Acciones</h2>
            <p class="field-hint" style="margin: 0;">
                @if($puedeActuarDecano)
                    Secretaría ya revisó este trámite. Aprueba o rechaza con firma de Decano.
                    Si rechazas, deberás indicar el motivo; quedará registrado en el trámite.
                @else
                    Aprueba o rechaza esta solicitud. Si rechazas, deberás indicar el motivo; quedará registrado en el trámite.
                @endif
            </p>
            <div class="row" style="gap: 0.65rem; flex-wrap: wrap;">
                <form method="POST" action="{{ $accionUrl }}" data-loading-label="Aprobando…">
                    @csrf
                    <input type="hidden" name="aprobado" value="1">
                    <button type="submit" class="btn btn--success">Aprobar</button>
                </form>
                <button type="button" class="btn btn--danger" data-rechazo-open>Rechazar</button>
            </div>
        </article>

        <div class="logout-modal" hidden data-rechazo-modal role="dialog" aria-modal="true" aria-labelledby="rechazo-modal-title">
            <button type="button" class="logout-modal__backdrop" aria-label="Cerrar" data-rechazo-close></button>
            <div class="logout-modal__panel">
                <form method="POST" action="{{ $accionUrl }}" data-loading-label="Rechazando…">
                    @csrf
                    <input type="hidden" name="aprobado" value="0">
                    <h2 id="rechazo-modal-title" class="logout-modal__title">Motivo del rechazo</h2>
                    <p class="logout-modal__text">
                        Indica el motivo por el cual rechazas esta solicitud. El comentario quedará registrado en el trámite.
                    </p>
                    <label class="visually-hidden" for="comentario-rechazo">Motivo del rechazo</label>
                    <textarea
                        id="comentario-rechazo"
                        name="{{ $campoObservacion }}"
                        rows="4"
                        required
                        maxlength="5000"
                        placeholder="Escriba el motivo del rechazo…"
                        style="width: 100%; margin-bottom: 1rem; resize: vertical;"
                    ></textarea>
                    <div class="logout-modal__actions">
                        <button type="button" class="btn btn--ghost" data-rechazo-close>Cancelar</button>
                        <button type="submit" class="btn btn--danger">Confirmar rechazo</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <article class="card solicitud-identity">
        <div class="solicitud-avatar" aria-hidden="true">{{ $iniciales }}</div>
        <div class="solicitud-identity__body">
            <p class="solicitud-identity__label">Solicitante</p>
            <h2 class="solicitud-identity__name">{{ $solicitud->creador->nombreCompleto() }}</h2>
            <p class="solicitud-identity__meta">
                {{ $solicitud->creador->email }}
                · {{ $solicitud->creador->rol->label() }}
                @if($solicitud->creador->carrera)
                    · {{ \App\Support\Carreras::label($solicitud->creador->carrera) }}
                @endif
            </p>
        </div>
    </article>

    <dl class="ficha-grid">
        <div class="ficha-item">
            <dt>Periodo</dt>
            <dd>{{ $solicitud->fecha_inicio->format('d/m/Y') }} – {{ $solicitud->fecha_fin->format('d/m/Y') }}</dd>
        </div>
        @if(!empty($d['hora_inicio']))
            <div class="ficha-item">
                <dt>Horario</dt>
                <dd>{{ $d['hora_inicio'] }} – {{ $d['hora_fin'] ?? '' }}</dd>
            </div>
        @endif
        @if(!empty($d['jornada']))
            <div class="ficha-item">
                <dt>Jornada</dt>
                <dd>{{ $d['jornada'] }}</dd>
            </div>
        @endif
        @if(!empty($d['codigo_tramite']))
            <div class="ficha-item">
                <dt>Código</dt>
                <dd>{{ $d['codigo_tramite'] }}</dd>
            </div>
        @endif
        @if($solicitud->fecha_firma)
            <div class="ficha-item">
                <dt>Fecha de resolución</dt>
                <dd>{{ $solicitud->fecha_firma->format('d/m/Y H:i') }}</dd>
            </div>
        @endif
        @if($solicitud->firmante)
            <div class="ficha-item">
                <dt>Firmado por</dt>
                <dd>{{ $solicitud->firmante->nombreCompleto() }}</dd>
            </div>
        @endif
        @if(!empty($d['institucion_medica']))
            <div class="ficha-item">
                <dt>Institución médica</dt>
                <dd>{{ $d['institucion_medica'] }}</dd>
            </div>
        @endif
        @if(!empty($d['medico_tratante']))
            <div class="ficha-item">
                <dt>Médico tratante</dt>
                <dd>{{ $d['medico_tratante'] }}</dd>
            </div>
        @endif
        @if(!empty($d['diagnostico']))
            <div class="ficha-item">
                <dt>Diagnóstico</dt>
                <dd>{{ $d['diagnostico'] }}</dd>
            </div>
        @endif
        @if(!empty($d['nombre_evento']))
            <div class="ficha-item">
                <dt>Evento</dt>
                <dd>{{ $d['nombre_evento'] }}</dd>
            </div>
        @endif
        @if(!empty($d['lugar_evento']))
            <div class="ficha-item">
                <dt>Lugar</dt>
                <dd>{{ $d['lugar_evento'] }}</dd>
            </div>
        @endif
        @if(!empty($d['destino']))
            <div class="ficha-item">
                <dt>Destino</dt>
                <dd>{{ $d['destino'] }}</dd>
            </div>
        @endif
        @if(!empty($d['tipo_calamidad']))
            <div class="ficha-item">
                <dt>Tipo de calamidad</dt>
                <dd>{{ $d['tipo_calamidad'] }}</dd>
            </div>
        @endif
        @if(!empty($d['nombre_familiar']))
            <div class="ficha-item">
                <dt>Familiar</dt>
                <dd>{{ $d['nombre_familiar'] }}{{ !empty($d['parentesco']) ? ' ('.$d['parentesco'].')' : '' }}</dd>
            </div>
        @endif
        @if(!empty($d['tipo_marcacion_omitida']))
            <div class="ficha-item">
                <dt>Marcación</dt>
                <dd>{{ $d['tipo_marcacion_omitida'] }}</dd>
            </div>
        @endif
        <div class="ficha-item ficha-item--wide">
            <dt>Motivo</dt>
            <dd>{{ $solicitud->motivo }}</dd>
        </div>
    </dl>

    <article class="card stack solicitud-preview">
        <div class="solicitud-preview__header">
            <h2 class="solicitud-preview__title">Vista previa de la solicitud</h2>
            <span class="solicitud-preview__tipo">{{ $solicitud->tipo->label() }}</span>
        </div>

        <div class="stack solicitud-preview__adjunto">
            <div>
                <h3 class="solicitud-section-title" style="font-size: 0.95rem;">Oficio institucional</h3>
                <p class="field-hint" style="margin: 0;">
                    Mismo contenido del oficio del trámite, en visor de documento.
                </p>
            </div>
            <x-doc-viewer
                title="Oficio institucional"
                :file-name="($d['codigo_tramite'] ?? 'oficio').'.pdf'"
                :src="route('solicitudes.preview-oficio', $solicitud)"
                kind="html"
            />
        </div>

        @forelse($documentos as $index => $documento)
            <div class="stack solicitud-preview__adjunto">
                <div>
                    <h3 class="solicitud-section-title" style="font-size: 0.95rem;">
                        {{ count($documentos) > 1 ? 'Documento de respaldo '.($index + 1) : 'Documento de respaldo' }}
                    </h3>
                    <p class="field-hint" style="margin: 0;">{{ $documento['nombre'] }}</p>
                </div>
                <x-doc-viewer
                    :title="$documento['nombre']"
                    :file-name="$documento['nombre']"
                    :src="$documento['url']"
                    :kind="$documento['kind']"
                />
            </div>
        @empty
            <p class="field-hint" style="margin: 0;">No hay documentos adjuntos.</p>
        @endforelse
    </article>

    @if($solicitud->observaciones_secretaria || $solicitud->observaciones_decano)
        <article class="card stack">
            <h2 class="solicitud-section-title">Observaciones</h2>
            <dl class="ficha-grid">
                @if($solicitud->observaciones_secretaria)
                    <div class="ficha-item ficha-item--wide">
                        <dt>Secretaría</dt>
                        <dd>{{ $solicitud->observaciones_secretaria }}</dd>
                    </div>
                @endif
                @if($solicitud->observaciones_decano)
                    <div class="ficha-item ficha-item--wide">
                        <dt>Decano</dt>
                        <dd>{{ $solicitud->observaciones_decano }}</dd>
                    </div>
                @endif
            </dl>
        </article>
    @endif

    <article class="card stack">
        <h2 class="solicitud-section-title">Historial del trámite</h2>
        <p class="field-hint" style="margin: 0;">
            Flujo completo. La fase actual queda resaltada.
        </p>
        <div class="tramite-timeline" role="list" aria-label="Fases del trámite">
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
                            <p class="tramite-timeline__label">{{ $event['label'] }}</p>
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
                        <p class="tramite-timeline__fecha">{{ $fmtFecha($event['fecha'] ?? null) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </article>
</section>
@endsection
