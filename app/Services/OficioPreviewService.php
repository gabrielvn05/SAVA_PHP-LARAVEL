<?php

namespace App\Services;

use App\Models\Solicitud;
use App\Models\User;

class OficioPreviewService
{
    public function render(Solicitud $solicitud, ?User $decano = null): string
    {
        $solicitud->loadMissing('creador');
        $decano ??= User::query()->where('rol', 'decano')->where('activo', true)->first();

        $detalle = $solicitud->detalle ?? [];
        $codigo = $detalle['codigo_tramite'] ?? strtoupper(substr($solicitud->id, 0, 8));

        return view('certificado.preview', [
            'solicitud' => $solicitud,
            'decano' => $decano,
            'codigo' => $codigo,
            'logoFacultad' => asset('templates/oficio-media/logo-facultad.png'),
            'marcaUleam' => asset('templates/oficio-media/marca-uleam.png'),
        ])->render();
    }
}
