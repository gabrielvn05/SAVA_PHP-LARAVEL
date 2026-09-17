@extends('certificado.partials.oficio-layout')

@section('asunto')
    Justificación por omisión o falla en el registro de asistencia biométrica (Face ID)
@endsection

@section('contenido')
    <p class="cuerpo">De mi consideración:</p>

    <p class="cuerpo">
        Yo, <strong>{{ $nombreCompleto }}</strong>, portador de la cédula de identidad N° <strong>{{ $cedula }}</strong>,
        docente de la carrera de <strong>{{ $carrera }}</strong> de la {{ $facultadNombre }}, por medio del presente documento
        justifico la falta o inconsistencia en mi marcación de asistencia mediante el sistema de reconocimiento facial (Face ID),
        conforme a los siguientes datos:
    </p>

    <div class="bloque-datos">
        <p><strong>Detalle del evento no registrado correctamente</strong></p>
        <p>Fecha del incidente: {{ $fechaIncidente }}</p>
        <p>Tipo de marcación omitida/fallida: {{ $tipoMarcacion }}</p>
        <p>Hora real de ingreso: {{ $horaIngreso }}</p>
        <p>Hora real de salida: {{ $horaSalida }}</p>
        <p>Motivo de la falta de registro: {{ $motivoFaltaRegistro }}</p>
        @if(filled($descripcionComplementaria))
            <p>Descripción complementaria: {{ $descripcionComplementaria }}</p>
        @endif
    </div>

    <p class="cuerpo">
        Declaro bajo mi compromiso académico que la información consignada es verídica y que en el horario indicado me encontraba
        efectivamente cumpliendo mis funciones docentes. Adjunto o haré llegar, de ser requerido, evidencia de mi presencia
        (registro de clases, listas de estudiantes, declaración de coordinador u otra prueba admisible según la normativa institucional).
    </p>
@endsection
