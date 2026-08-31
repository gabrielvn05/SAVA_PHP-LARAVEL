<?php

namespace App\Services;

use App\Enums\AccountRequestStatus;
use App\Enums\AppRole;
use App\Enums\SolicitudEstado;
use App\Models\AccountRequest;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardStatsService
{
    public function forUser(User $user): array
    {
        $query = Solicitud::query();

        $esStaff = in_array($user->rol, [AppRole::Secretaria, AppRole::Decano], true);

        if (! $esStaff) {
            $query->where('creado_por', $user->id);
        }

        $total = (clone $query)->count();
        $aprobadas = (clone $query)->where('estado', SolicitudEstado::Aprobada)->count();
        $rechazadas = (clone $query)->where('estado', SolicitudEstado::Rechazada)->count();
        $enProceso = (clone $query)->whereIn('estado', [
            SolicitudEstado::EnBorrador,
            SolicitudEstado::EnRevisionSecretaria,
            SolicitudEstado::PendienteAprobacionDecano,
        ])->count();

        $recientes30Dias = (clone $query)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $tasaAprobacion = $total > 0 ? round(($aprobadas / $total) * 100, 1) : 0;
        $tasaResolucion = $total > 0 ? round((($aprobadas + $rechazadas) / $total) * 100, 1) : 0;

        $porEstado = (clone $query)
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $stats = [
            'total' => $total,
            'aprobadas' => $aprobadas,
            'rechazadas' => $rechazadas,
            'enProceso' => $enProceso,
            'recientes30Dias' => $recientes30Dias,
            'tasaAprobacion' => $tasaAprobacion,
            'tasaResolucion' => $tasaResolucion,
            'porEstado' => $porEstado,
            'scopeLabel' => $esStaff ? 'Institución' : 'Mis trámites',
        ];

        if (in_array($user->rol, [AppRole::Decano, AppRole::Secretaria], true)) {
            $stats['solicitudesCuentaPendientes'] = AccountRequest::query()
                ->where('status', AccountRequestStatus::Pendiente)
                ->count();
        }

        return $stats;
    }
}
