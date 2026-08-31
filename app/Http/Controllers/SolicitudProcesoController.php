<?php

namespace App\Http\Controllers;

use App\Enums\CapabilityType;
use App\Enums\SolicitudEstado;
use App\Models\Solicitud;
use App\Services\AuditService;
use App\Services\SolicitudWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolicitudProcesoController extends Controller
{
    public function __construct(
        private readonly SolicitudWorkflowService $workflow,
        private readonly AuditService $audit,
    ) {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (! $user->hasCapability(CapabilityType::RevisarSolicitudes)
                && ! $user->hasCapability(CapabilityType::AprobarSolicitudes)) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(): View
    {
        $solicitudes = Solicitud::query()
            ->with('creador')
            ->whereIn('estado', [
                SolicitudEstado::EnRevisionSecretaria,
                SolicitudEstado::PendienteAprobacionDecano,
            ])
            ->orderByDesc('created_at')
            ->get();

        return view('solicitudes.proceso', compact('solicitudes'));
    }

    public function revisar(Request $request, Solicitud $solicitud): RedirectResponse
    {
        $this->authorize('revisar', $solicitud);

        $validated = $request->validate([
            'aprobado' => 'required|boolean',
            'observaciones_secretaria' => 'nullable|string|max:5000',
        ]);

        $old = $solicitud->toArray();
        $nuevoEstado = $this->workflow->estadoTrasRevisionSecretaria(
            $solicitud->creador->rol,
            (bool) $validated['aprobado'],
        );

        $solicitud->update([
            'estado' => $nuevoEstado,
            'revisado_por' => auth()->id(),
            'observaciones_secretaria' => $validated['observaciones_secretaria'] ?? null,
            'firmado_por' => $nuevoEstado === SolicitudEstado::Aprobada ? auth()->id() : null,
            'fecha_firma' => $nuevoEstado === SolicitudEstado::Aprobada ? now() : null,
        ]);

        $this->audit->log('UPDATE', $solicitud, $old);

        return back()->with('success', 'Revisión registrada.');
    }

    public function aprobar(Request $request, Solicitud $solicitud): RedirectResponse
    {
        $this->authorize('aprobar', $solicitud);

        $validated = $request->validate([
            'aprobado' => 'required|boolean',
            'observaciones_decano' => 'nullable|string|max:5000',
        ]);

        $old = $solicitud->toArray();
        $aprobado = (bool) $validated['aprobado'];

        $solicitud->update([
            'estado' => $aprobado ? SolicitudEstado::Aprobada : SolicitudEstado::Rechazada,
            'firmado_por' => auth()->id(),
            'observaciones_decano' => $validated['observaciones_decano'] ?? null,
            'fecha_firma' => $aprobado ? now() : null,
        ]);

        $this->audit->log('UPDATE', $solicitud, $old);

        return back()->with('success', $aprobado ? 'Solicitud aprobada.' : 'Solicitud rechazada.');
    }
}
