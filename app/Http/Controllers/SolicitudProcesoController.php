<?php

namespace App\Http\Controllers;

use App\Enums\CapabilityType;
use App\Enums\SolicitudEstado;
use App\Enums\SolicitudTipo;
use App\Models\Solicitud;
use App\Services\AuditService;
use App\Services\SolicitudWorkflowService;
use App\Support\SolicitudValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function index(Request $request): View
    {
        $query = Solicitud::query()->with('creador');

        if ($estado = $request->string('estado')->toString()) {
            $query->where('estado', $estado);
        }

        if ($tipo = $request->string('tipo')->toString()) {
            $query->where('tipo', $tipo);
        }

        if ($q = trim($request->string('q')->toString())) {
            $query->where(function ($sub) use ($q): void {
                $sub->where('motivo', 'ilike', "%{$q}%")
                    ->orWhereHas('creador', function ($user) use ($q): void {
                        $user->where('nombres', 'ilike', "%{$q}%")
                            ->orWhere('apellidos', 'ilike', "%{$q}%");
                    });
            });
        }

        if (! $request->hasAny(['estado', 'tipo', 'q'])) {
            $query->whereIn('estado', [
                SolicitudEstado::EnRevisionSecretaria,
                SolicitudEstado::PendienteAprobacionDecano,
            ]);
        }

        $solicitudes = $query->orderByDesc('created_at')->get();

        return view('solicitudes.proceso', [
            'solicitudes' => $solicitudes,
            'tipos' => SolicitudTipo::cases(),
            'estados' => SolicitudEstado::cases(),
            'filtros' => $request->only(['estado', 'tipo', 'q']),
        ]);
    }

    public function revisar(Request $request, Solicitud $solicitud): RedirectResponse
    {
        $this->authorize('revisar', $solicitud);

        if ($solicitud->creado_por === auth()->id()) {
            return back()->with('error', 'No puedes revisar una solicitud creada por ti mismo.');
        }

        $validated = $request->validate([
            'aprobado' => 'required|boolean',
            'observaciones_secretaria' => 'nullable|string|max:5000',
        ]);

        if (! $validated['aprobado'] && blank($validated['observaciones_secretaria'] ?? null)) {
            return back()->with('error', 'Debe indicar el motivo del rechazo.');
        }

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

        if ($solicitud->creado_por === auth()->id()) {
            return back()->with('error', 'No puedes firmar una solicitud creada por ti mismo.');
        }

        $validated = $request->validate([
            'aprobado' => 'required|boolean',
            'observaciones_decano' => 'nullable|string|max:5000',
        ]);

        if (! $validated['aprobado'] && blank($validated['observaciones_decano'] ?? null)) {
            return back()->with('error', 'Debe indicar el motivo del rechazo.');
        }

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
