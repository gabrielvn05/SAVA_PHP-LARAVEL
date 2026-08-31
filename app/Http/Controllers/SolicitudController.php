<?php

namespace App\Http\Controllers;

use App\Enums\CapabilityType;
use App\Enums\SolicitudEstado;
use App\Enums\SolicitudTipo;
use App\Models\Solicitud;
use App\Services\AuditService;
use App\Services\SolicitudWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SolicitudController extends Controller
{
    public function __construct(
        private readonly SolicitudWorkflowService $workflow,
        private readonly AuditService $audit,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Solicitud::class);

        $solicitudes = Solicitud::query()
            ->with('creador')
            ->where('creado_por', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        return view('solicitudes.index', compact('solicitudes'));
    }

    public function create(): View
    {
        $this->authorize('create', Solicitud::class);

        return view('solicitudes.create', [
            'tipos' => SolicitudTipo::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Solicitud::class);

        $validated = $request->validate([
            'tipo' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'motivo' => 'required|string|max:5000',
            'justificativo' => 'nullable|file|max:10240',
        ]);

        $path = null;
        $nombre = null;
        if ($request->hasFile('justificativo')) {
            $file = $request->file('justificativo');
            $path = $file->store('justificativos', 'public');
            $nombre = $file->getClientOriginalName();
        }

        $user = auth()->user();
        $estado = $request->boolean('borrador')
            ? SolicitudEstado::EnBorrador
            : $this->workflow->estadoInicial($user->rol);

        $solicitud = Solicitud::create([
            'creado_por' => $user->id,
            'tipo' => $validated['tipo'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'motivo' => $validated['motivo'],
            'justificativo_path' => $path,
            'justificativo_nombre' => $nombre,
            'estado' => $estado,
            'firmado_por' => $this->workflow->solicitudCreadaPorDecano($user->rol) ? $user->id : null,
            'fecha_firma' => $this->workflow->solicitudCreadaPorDecano($user->rol) ? now() : null,
        ]);

        $this->audit->log('INSERT', $solicitud);

        return redirect()->route('solicitudes.show', $solicitud)
            ->with('success', 'Solicitud registrada correctamente.');
    }

    public function show(Solicitud $solicitud): View
    {
        $this->authorize('view', $solicitud);
        $solicitud->load(['creador', 'revisor', 'firmante']);

        return view('solicitudes.show', compact('solicitud'));
    }

    public function edit(Solicitud $solicitud): View
    {
        $this->authorize('update', $solicitud);

        return view('solicitudes.edit', [
            'solicitud' => $solicitud,
            'tipos' => SolicitudTipo::cases(),
        ]);
    }

    public function update(Request $request, Solicitud $solicitud): RedirectResponse
    {
        $this->authorize('update', $solicitud);

        $validated = $request->validate([
            'tipo' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'motivo' => 'required|string|max:5000',
            'justificativo' => 'nullable|file|max:10240',
        ]);

        $old = $solicitud->toArray();

        $data = [
            'tipo' => $validated['tipo'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'motivo' => $validated['motivo'],
        ];

        if ($request->hasFile('justificativo')) {
            if ($solicitud->justificativo_path) {
                Storage::disk('public')->delete($solicitud->justificativo_path);
            }
            $file = $request->file('justificativo');
            $data['justificativo_path'] = $file->store('justificativos', 'public');
            $data['justificativo_nombre'] = $file->getClientOriginalName();
        }

        if ($solicitud->estado === SolicitudEstado::EnBorrador && ! $request->boolean('borrador')) {
            $data['estado'] = $this->workflow->estadoInicial(auth()->user()->rol);
        }

        $solicitud->update($data);
        $this->audit->log('UPDATE', $solicitud, $old);

        return redirect()->route('solicitudes.show', $solicitud)
            ->with('success', 'Solicitud actualizada.');
    }

    public function destroy(Solicitud $solicitud): RedirectResponse
    {
        $this->authorize('delete', $solicitud);

        $old = $solicitud->toArray();
        if ($solicitud->justificativo_path) {
            Storage::disk('public')->delete($solicitud->justificativo_path);
        }
        $solicitud->delete();
        $this->audit->log('DELETE', $solicitud, $old);

        return redirect()->route('solicitudes.index')
            ->with('success', 'Borrador eliminado.');
    }
}
