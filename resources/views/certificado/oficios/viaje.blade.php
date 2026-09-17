@extends('certificado.partials.oficio-layout')

@section('asunto')
    Justificación de faltas por viaje académico
@endsection

@section('contenido')
    <p class="cuerpo">De mi consideración:</p>

    <p class="cuerpo">
        Yo, <strong>{{ $nombreCompleto }}</strong>, portador de la cédula de identidad N° <strong>{{ $cedula }}</strong>,
        docente de la carrera de <strong>{{ $carrera }}</strong> de la {{ $facultadNombre }}, por medio del presente documento formal
        justifico mi ausencia a mis actividades académicas por motivo de viaje académico, conforme a los siguientes datos:
    </p>

    <p class="cuerpo">
        Período de la falta: desde el día <strong>{{ $fechaInicio }}</strong> hasta el día <strong>{{ $fechaFin }}</strong>,
        lo que corresponde a un total de <strong>{{ $numeroDias }}</strong> días calendario de ausencia.
    </p>

    <div class="bloque-datos">
        <p><strong>Detalles del viaje académico</strong></p>
        <p>Tipo de viaje: {{ $tipoViaje }}</p>
        <p>Nombre del evento o institución de estudio: {{ $nombreEvento }}</p>
        <p>Lugar (ciudad, país): {{ $lugarEvento }}</p>
        <p>Fechas del evento o actividad: {{ $fechasEventoActividad }}</p>
        @if(filled($rolEspecifico))
            <p>Rol específico: {{ $rolEspecifico }}</p>
        @endif
        @if(filled($objetivoAcademico))
            <p>Breve descripción del objetivo académico: {{ $objetivoAcademico }}</p>
        @endif
    </div>

    <p class="cuerpo">
        Declaro bajo mi compromiso académico que la información aquí consignada es verídica. Me comprometo a reincorporarme a mis labores
        en la fecha de retorno indicada, así como a presentar al Decanato un informe de las actividades realizadas (incluyendo constancias,
        certificados o programa del evento) en un plazo no mayor a 8 días hábiles posteriores a mi retorno, y a realizar las gestiones de
        nivelación con mis estudiantes según lo establecido por la normativa institucional.
    </p>
@endsection
