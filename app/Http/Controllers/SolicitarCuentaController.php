<?php

namespace App\Http\Controllers;

use App\Enums\AccountRequestStatus;
use App\Enums\AppRole;
use App\Models\AccountRequest;
use App\Models\User;
use App\Services\AccountRequestValidator;
use App\Support\Carreras;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolicitarCuentaController extends Controller
{
    public function __construct(private readonly AccountRequestValidator $validator) {}

    public function create(Request $request): View
    {
        return view('solicitar-cuenta.create', [
            'aviso' => $request->query('aviso'),
            'carreras' => Carreras::OPCIONES,
            'roles' => [
                AppRole::Docente,
                AppRole::Administrativo,
                AppRole::Mantenimiento,
                AppRole::Secretaria,
                AppRole::Decano,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $result = $this->validator->validate($request->all());

        if (! $result['ok']) {
            return redirect()->route('solicitar-cuenta.create', ['aviso' => $result['aviso']]);
        }

        $data = $result['data'];

        if (User::query()->where('email', $data['email'])->exists()) {
            return redirect()->route('solicitar-cuenta.create', ['aviso' => 'usuario_existe']);
        }

        if (AccountRequest::query()
            ->where('email', $data['email'])
            ->where('status', AccountRequestStatus::Pendiente)
            ->exists()) {
            return redirect()->route('solicitar-cuenta.create', ['aviso' => 'solicitud_pendiente']);
        }

        try {
            AccountRequest::create([
                ...$data,
                'status' => AccountRequestStatus::Pendiente,
            ]);
        } catch (\Throwable) {
            return redirect()->route('solicitar-cuenta.create', ['aviso' => 'solicitud_pendiente']);
        }

        return redirect()->route('login', ['solicitud' => 'ok']);
    }
}
