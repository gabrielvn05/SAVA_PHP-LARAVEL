<?php

namespace App\Services;

use App\Enums\SolicitudTipo;
use App\Models\Solicitud;
use App\Models\User;

class OficioPreviewService
{
    public function __construct(
        private readonly OficioDatosService $datos,
        private readonly OficioDocxService $docx,
    ) {}

    public function puedeUsarPlantillaDocx(Solicitud $solicitud): bool
    {
        return $this->docx->puedeGenerarDocx($solicitud);
    }

    public function renderHtmlRespaldo(Solicitud $solicitud, ?User $decano = null): string
    {
        $solicitud->loadMissing('creador');
        $decano ??= User::query()->where('rol', 'decano')->where('activo', true)->first();

        $detalle = $solicitud->detalle ?? [];
        $valores = $this->datos->placeholders($solicitud, $decano);
        $codigo = $valores['[Código del oficio]'] ?? strtoupper(substr($solicitud->id, 0, 8));

        $vista = match ($solicitud->tipo) {
            SolicitudTipo::FaltaMarcado => 'certificado.oficios.reconocimiento',
            SolicitudTipo::CalamidadDomestica => 'certificado.oficios.calamidad',
            SolicitudTipo::Enfermedad => 'certificado.oficios.medico',
            SolicitudTipo::Viaje => 'certificado.oficios.viaje',
            default => 'certificado.preview',
        };

        $base = [
            'solicitud' => $solicitud,
            'decano' => $decano,
            'codigo' => $codigo,
            'facultadNombre' => OficioDatosService::FACULTAD_NOMBRE,
            'nombreCompleto' => $valores['[Nombre completo del docente]'],
            'cedula' => $valores['[Cédula]'],
            'carrera' => $valores['[Carrera]'],
            'correo' => $valores['_correo_docente'] ?? $solicitud->creador->email,
            'rolPersonal' => $detalle['tipo_personal'] ?? $solicitud->creador->rol->label(),
            'fechaGeneracion' => $valores['[Fecha automática del sistema]'],
        ];

        if ($vista === 'certificado.preview') {
            return view($vista, array_merge($base, [
                'logoFacultad' => asset('templates/oficio-media/logo-facultad.png'),
                'marcaUleam' => asset('templates/oficio-media/marca-uleam.png'),
            ]))->render();
        }

        return view($vista, array_merge($base, $this->extrasHtml($solicitud, $valores)))->render();
    }

    /**
     * @param  array<string, string>  $valores
     * @return array<string, mixed>
     */
    private function extrasHtml(Solicitud $solicitud, array $valores): array
    {
        return match ($solicitud->tipo) {
            SolicitudTipo::FaltaMarcado => [
                'fechaIncidente' => $valores['[Fecha del incidente]'],
                'tipoMarcacion' => $valores['[Tipo de marcación omitida/fallida]'],
                'horaIngreso' => $valores['[Hora real de ingreso]'],
                'horaSalida' => $valores['[Hora real de salida]'],
                'motivoFaltaRegistro' => $valores['[Motivo de la falta de registro]'],
                'descripcionComplementaria' => $valores['[Descripción complementaria]'] !== '—'
                    ? $valores['[Descripción complementaria]']
                    : null,
            ],
            SolicitudTipo::CalamidadDomestica => [
                'fechaInicio' => $valores['[Fecha de inicio de la falta]'],
                'fechaFin' => $valores['[Fecha de retorno a clases]'],
                'numeroDias' => $valores['[Número de días]'],
                'tipoCalamidad' => $valores['[Tipo de calamidad]'],
                'nombreFamiliar' => $valores['[Nombre completo del familiar afectado]'],
                'parentesco' => $valores['[Parentesco]'],
                'descripcionHecho' => $valores['[Descripción del hecho]'],
                'lugarSuceso' => $valores['[Lugar donde ocurrió]'],
                'fechaHecho' => $valores['[Fecha del hecho]'],
            ],
            SolicitudTipo::Enfermedad => [
                'fechaInicio' => $valores['[Fecha de inicio de la falta]'],
                'fechaFin' => $valores['[Fecha de retorno a clases]'],
                'numeroDias' => $valores['[Número de días]'],
                'institucionMedica' => $valores['[Lugar donde se realizó la atención médica]'],
                'medicoTratante' => $valores['[Nombre completo del doctor(a)]'],
                'diagnostico' => $valores['[Diagnóstico principal]'],
            ],
            SolicitudTipo::Viaje => [
                'fechaInicio' => $valores['[Fecha de inicio de la falta]'],
                'fechaFin' => $valores['[Fecha de retorno a clases]'],
                'numeroDias' => $valores['[Número de días]'],
                'tipoViaje' => $valores['[Tipo de viaje]'],
                'nombreEvento' => $valores['[Nombre del evento o institución de estudio]'],
                'lugarEvento' => $valores['[Lugar (ciudad, país)]'],
                'fechasEventoActividad' => $valores['[Fechas del evento o actividad]'],
                'rolEspecifico' => ($valores['[Rol específico]'] ?? '—') !== '—' ? $valores['[Rol específico]'] : null,
                'objetivoAcademico' => ($valores['[Objetivo académico]'] ?? '—') !== '—' ? $valores['[Objetivo académico]'] : null,
            ],
            default => [],
        };
    }
}
