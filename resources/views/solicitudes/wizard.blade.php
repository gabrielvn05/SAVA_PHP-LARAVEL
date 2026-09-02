@extends('layouts.app')

@section('title', 'Nueva solicitud')

@php
    $perfilCompleto = trim($user->cedula) !== '' && trim($user->carrera) !== '';
@endphp

@section('content')
<section class="stack page-enter">
    <header class="page-header">
        <div class="page-header__text">
            <h1 class="page-header__title">Nueva solicitud</h1>
        </div>
        <div class="page-header__actions">
            <a href="{{ route('solicitudes.index') }}" class="btn btn--secondary btn--sm">Volver</a>
            <a href="{{ route('solicitudes.create') }}" class="btn btn--secondary btn--sm">Formulario simple</a>
        </div>
    </header>

    <div class="wizard-progress" aria-hidden="true">
        <div class="wizard-progress__step is-done" id="wizard-progress-step-0"></div>
        <div class="wizard-progress__step" id="wizard-progress-step-1"></div>
    </div>

    <form method="POST" action="{{ route('solicitudes.store') }}" enctype="multipart/form-data" class="stack" id="wizard-form" data-loading-label="Enviando solicitud…">
        @csrf
        <input type="hidden" name="tipo" id="tipo-input" value="{{ old('tipo') }}" required>

        <article class="card stack wizard-panel" id="panel-tipo">
            <h2 style="margin: 0;">Paso 1: Tipo de solicitud</h2>
            <p class="field-hint" style="margin-top: 0;">
                Selecciona la opción que mejor describa tu caso. Esto define los campos del formulario de la solicitud.
            </p>

            <div class="wizard-tipo-grid">
                @foreach($tipos as $tipo)
                    <button type="button" class="wizard-tipo-card" data-tipo="{{ $tipo->value }}">
                        <strong class="wizard-tipo-card__title">{{ $tipo->label() }}</strong>
                        <span class="field-hint wizard-tipo-card__desc">{{ $tipo->description() }}</span>
                    </button>
                @endforeach
            </div>
            @error('tipo') <p class="field-hint" style="color: var(--color-danger);">{{ $message }}</p> @enderror

            <div class="row" style="justify-content: flex-end;">
                <button class="btn btn--primary" type="button" data-wizard-continue disabled>Continuar</button>
            </div>
        </article>

        <article class="card stack wizard-panel" id="panel-datos" hidden>
            <h2 style="margin: 0;">Paso 2: Datos del trámite</h2>
            <p class="field-hint" style="margin-top: 0;" id="wizard-tipo-desc">
                La fecha de inasistencia no puede ser anterior a {{ $minFecha }} (ventana de 3 meses).
            </p>

            <p class="field-hint" style="margin-top: 0; font-weight: 600; color: var(--color-text);">
                La solicitud se registrará a nombre de: {{ $user->nombreCompleto() }} ({{ $user->rol->label() }})
            </p>

            @if($perfilCompleto)
                <article class="card card--flat" style="padding: 0.85rem 1rem; background: var(--color-surface-muted, #f6f8fb);">
                    <p class="field-hint" style="margin: 0 0 0.5rem; font-weight: 600; color: var(--color-text);">
                        Datos institucionales (desde su cuenta)
                    </p>
                    <div class="form-grid form-grid--2" style="gap: 0.35rem 1rem;">
                        <div>
                            <span class="field-hint">Cédula</span>
                            <div>{{ $user->cedula }}</div>
                        </div>
                        <div>
                            <span class="field-hint">Carrera</span>
                            <div>{{ \App\Support\Carreras::label($user->carrera) }}</div>
                        </div>
                    </div>
                </article>
            @else
                <div class="alert alert--warning" role="alert">
                    Completa tu cédula y carrera en
                    <a href="{{ route('perfil.edit') }}" style="font-weight: 700;">Perfil y configuración</a>
                    antes de crear solicitudes.
                </div>
            @endif

            <div class="wizard-field wizard-field--inasistencia" hidden>
                <label>Fecha de inasistencia *</label>
                <input type="date" name="fecha_inasistencia" min="{{ $minFecha }}" value="{{ old('fecha_inasistencia') }}">
                <p class="field-hint">Se valida contra la fecha actual (máximo 3 meses hacia atrás).</p>
            </div>

            <div class="form-grid form-grid--2 wizard-field wizard-field--fechas" hidden>
                <div>
                    <label>Fecha inicio *</label>
                    <input type="date" name="fecha_inicio" min="{{ $minFecha }}" value="{{ old('fecha_inicio') }}">
                </div>
                <div>
                    <label>Fecha fin *</label>
                    <input type="date" name="fecha_fin" min="{{ $minFecha }}" value="{{ old('fecha_fin') }}">
                </div>
            </div>

            <div class="form-grid form-grid--2 wizard-field wizard-field--permiso" hidden>
                <div>
                    <label>Hora inicio</label>
                    <input type="time" name="hora_inicio" value="{{ old('hora_inicio') }}">
                </div>
                <div>
                    <label>Hora fin</label>
                    <input type="time" name="hora_fin" value="{{ old('hora_fin') }}">
                </div>
            </div>

            <div class="wizard-field wizard-field--enfermedad" hidden>
                <hr style="border: 0; border-top: 1px solid var(--color-border);">
                <h3 style="margin: 0;">Datos del certificado médico</h3>
                <div class="form-grid form-grid--2">
                    <div>
                        <label>Institución médica *</label>
                        <select name="institucion_medica_tipo" id="institucion-medica-tipo">
                            <option value="">Seleccionar</option>
                            @foreach(['IESS', 'Ministerio de Salud Pública', 'Hospital privado', 'Centro de salud', 'Otro'] as $inst)
                                <option value="{{ $inst }}" @selected(old('institucion_medica_tipo') === $inst)>{{ $inst }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="wizard-field wizard-field--institucion-nombre" hidden>
                        <label>Nombre del establecimiento *</label>
                        <input type="text" name="institucion_medica_nombre" value="{{ old('institucion_medica_nombre') }}" placeholder="Ej: Hospital Metropolitano">
                    </div>
                    <div>
                        <label>Médico tratante *</label>
                        <input type="text" name="medico_tratante" value="{{ old('medico_tratante') }}" placeholder="Dr. Juan Pérez">
                    </div>
                    <div>
                        <label>Fecha de emisión del certificado *</label>
                        <input type="date" name="fecha_emision_certificado" value="{{ old('fecha_emision_certificado') }}">
                    </div>
                    <div>
                        <label>Días de reposo</label>
                        <input type="text" name="dias_reposo" value="{{ old('dias_reposo') }}" inputmode="numeric" placeholder="Ej: 3">
                        <p class="field-hint">Si no aplica, déjalo vacío (se usará el mismo día de la inasistencia).</p>
                    </div>
                </div>
                <div>
                    <label>Diagnóstico *</label>
                    <textarea name="diagnostico" rows="4">{{ old('diagnostico') }}</textarea>
                </div>
            </div>

            <div class="wizard-field wizard-field--viaje" hidden>
                <hr style="border: 0; border-top: 1px solid var(--color-border);">
                <h3 style="margin: 0;">Datos del permiso por viaje</h3>
                <div class="form-grid form-grid--2">
                    <div>
                        <label>Fecha inicio de la falta *</label>
                        <input type="date" name="fecha_inicio_viaje" min="{{ $minFecha }}" value="{{ old('fecha_inicio_viaje') }}">
                    </div>
                    <div>
                        <label>Fecha de retorno *</label>
                        <input type="date" name="fecha_fin_viaje" min="{{ $minFecha }}" value="{{ old('fecha_fin_viaje') }}">
                    </div>
                </div>
                <div>
                    <label>Tipo de viaje *</label>
                    <select name="tipo_viaje_evento">
                        <option value="">Seleccionar</option>
                        <option value="estudio" @selected(old('tipo_viaje_evento') === 'estudio')>Estudio</option>
                        <option value="congreso_expositor" @selected(old('tipo_viaje_evento') === 'congreso_expositor')>Congreso – Expositor</option>
                        <option value="congreso_participante" @selected(old('tipo_viaje_evento') === 'congreso_participante')>Congreso – Participante (observador)</option>
                    </select>
                </div>
                <div>
                    <label>Nombre del evento o estudio *</label>
                    <input type="text" name="nombre_evento" value="{{ old('nombre_evento') }}">
                </div>
                <div>
                    <label>Lugar (ciudad, país) *</label>
                    <input type="text" name="lugar_evento" value="{{ old('lugar_evento') }}" placeholder="Ej: Quito, Ecuador">
                </div>
                <div>
                    <label>Rol específico (si aplica)</label>
                    <input type="text" name="rol_especifico" value="{{ old('rol_especifico') }}" placeholder="Ej: Ponente principal">
                    <p class="field-hint">Opcional.</p>
                </div>
            </div>

            <div class="wizard-field wizard-field--calamidad" hidden>
                <hr style="border: 0; border-top: 1px solid var(--color-border);">
                <h3 style="margin: 0;">Datos de calamidad doméstica</h3>
                <div>
                    <label>Tipo de calamidad *</label>
                    <select name="tipo_calamidad">
                        <option value="">Seleccionar</option>
                        <option value="fallecimiento_familiar" @selected(old('tipo_calamidad') === 'fallecimiento_familiar')>Fallecimiento de familiar</option>
                        <option value="emergencia_medica_familiar" @selected(old('tipo_calamidad') === 'emergencia_medica_familiar')>Emergencia médica grave de familiar</option>
                    </select>
                </div>
                <div>
                    <label>Nombre completo del familiar afectado *</label>
                    <input type="text" name="nombre_familiar" value="{{ old('nombre_familiar') }}">
                </div>
                <div>
                    <label>Parentesco (hasta segundo grado) *</label>
                    <select name="parentesco">
                        <option value="">Seleccionar</option>
                        <option value="conyuge" @selected(old('parentesco') === 'conyuge')>Cónyuge</option>
                        <option value="madre" @selected(old('parentesco') === 'madre')>Madre</option>
                        <option value="padre" @selected(old('parentesco') === 'padre')>Padre</option>
                        <option value="hermano" @selected(old('parentesco') === 'hermano')>Hermano(a)</option>
                        <option value="hijo" @selected(old('parentesco') === 'hijo')>Hijo(a)</option>
                    </select>
                </div>
                <div>
                    <label>Descripción del hecho *</label>
                    <textarea name="descripcion_hecho" rows="4">{{ old('descripcion_hecho') }}</textarea>
                </div>
                <div>
                    <label>Lugar donde ocurrió *</label>
                    <input type="text" name="lugar_suceso" value="{{ old('lugar_suceso') }}">
                </div>
                <div>
                    <label>Fecha del hecho *</label>
                    <input type="date" name="fecha_hecho" value="{{ old('fecha_hecho') }}">
                </div>
            </div>

            <div class="wizard-field wizard-field--falta" hidden>
                <hr style="border: 0; border-top: 1px solid var(--color-border);">
                <h3 style="margin: 0;">Reporte de novedad en marcación</h3>
                <p class="field-hint" style="margin: 0;">Justificante por olvidos o fallas del sistema Face ID.</p>
                <div>
                    <label>Jornada *</label>
                    <select name="jornada">
                        <option value="">Seleccionar jornada</option>
                        <option value="primera_jornada" @selected(old('jornada') === 'primera_jornada')>Primera jornada</option>
                        <option value="segunda_jornada" @selected(old('jornada') === 'segunda_jornada')>Segunda jornada</option>
                        <option value="ambas" @selected(old('jornada') === 'ambas')>Ambas jornadas</option>
                    </select>
                </div>
                <div>
                    <label>Fecha del incidente *</label>
                    <input type="date" name="fecha_incidente" min="{{ $minFecha }}" value="{{ old('fecha_incidente') }}">
                </div>
                <div>
                    <label>Tipo de marcación omitida/fallida *</label>
                    <select name="tipo_marcacion_omitida">
                        <option value="">Seleccionar</option>
                        <option value="entrada" @selected(old('tipo_marcacion_omitida') === 'entrada')>Marcación de entrada</option>
                        <option value="salida" @selected(old('tipo_marcacion_omitida') === 'salida')>Marcación de salida</option>
                    </select>
                </div>
                <div class="form-grid form-grid--2">
                    <div>
                        <label>Hora real de ingreso *</label>
                        <input type="time" name="hora_real_ingreso" value="{{ old('hora_real_ingreso') }}">
                    </div>
                    <div>
                        <label>Hora real de salida *</label>
                        <input type="time" name="hora_real_salida" value="{{ old('hora_real_salida') }}">
                    </div>
                </div>
                <div>
                    <label>Motivo de la falta de registro *</label>
                    <select name="motivo_falta_registro">
                        <option value="">Seleccionar</option>
                        <option value="olvido_docente" @selected(old('motivo_falta_registro') === 'olvido_docente')>Olvido del docente</option>
                        <option value="falla_face_id" @selected(old('motivo_falta_registro') === 'falla_face_id')>Falla técnica del sistema Face ID</option>
                    </select>
                </div>
                <div>
                    <label>Descripción complementaria (opcional)</label>
                    <textarea name="descripcion_complementaria" rows="3">{{ old('descripcion_complementaria') }}</textarea>
                </div>
            </div>

            <div class="wizard-field wizard-field--motivo">
                <label>Motivo / observaciones</label>
                <textarea name="motivo" rows="3">{{ old('motivo') }}</textarea>
                <p class="field-hint">En trámites guiados se genera automáticamente si lo dejas vacío.</p>
            </div>

            <div>
                <label>Observaciones adicionales</label>
                <textarea name="observaciones" rows="3">{{ old('observaciones') }}</textarea>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--color-border);">
            <h3 style="margin: 0;">Documento de respaldo</h3>
            <p class="field-hint" style="margin: 0;">
                Al enviar la solicitud se podrá consultar el oficio institucional desde el detalle del trámite.
            </p>
            <div>
                <label id="anexo-label">Adjuntar documento de respaldo</label>
                <input type="file" name="justificativo" accept=".pdf,.png,.jpg,.jpeg">
                <p class="field-hint">PDF, PNG o JPG. Puede agregar anexos extra a continuación.</p>
            </div>
            <div>
                <label>Anexos adicionales</label>
                <input type="file" name="anexos[]" multiple accept=".pdf,.png,.jpg,.jpeg">
            </div>

            <div class="row" style="justify-content: space-between;">
                <button class="btn btn--secondary" type="button" data-wizard-back>Atrás</button>
                <div class="row" style="gap: 8px;">
                    <button type="submit" name="borrador" value="1" class="btn btn--secondary">Guardar borrador</button>
                    <button type="submit" class="btn btn--primary">Enviar solicitud</button>
                </div>
            </div>
        </article>
    </form>
</section>
@endsection
