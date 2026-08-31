<?php

namespace App\Enums;

enum CapabilityType: string
{
    case GestionarUsuarios = 'gestionar_usuarios';
    case RevisarSolicitudes = 'revisar_solicitudes';
    case AprobarSolicitudes = 'aprobar_solicitudes';
    case GenerarSolicitudes = 'generar_solicitudes';

    public function label(): string
    {
        return match ($this) {
            self::GestionarUsuarios => 'Gestionar usuarios',
            self::RevisarSolicitudes => 'Revisar solicitudes',
            self::AprobarSolicitudes => 'Aprobar solicitudes',
            self::GenerarSolicitudes => 'Generar solicitudes',
        };
    }
}
