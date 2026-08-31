<?php

namespace App\Enums;

enum SolicitudTipo: string
{
    case Permiso = 'permiso';
    case Justificacion = 'justificacion';
    case Viaje = 'viaje';
    case Enfermedad = 'enfermedad';
    case CalamidadDomestica = 'calamidad_domestica';
    case FaltaMarcado = 'falta_marcado';

    public function label(): string
    {
        return match ($this) {
            self::Permiso => 'Permiso',
            self::Justificacion => 'Justificación',
            self::Viaje => 'Viaje',
            self::Enfermedad => 'Enfermedad',
            self::CalamidadDomestica => 'Calamidad doméstica',
            self::FaltaMarcado => 'Falta de marcado',
        };
    }
}
