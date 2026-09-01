<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Services\OficioPreviewService;
use Illuminate\Http\Response;

class CertificadoController extends Controller
{
    public function __construct(private readonly OficioPreviewService $previewService) {}

    public function preview(Solicitud $solicitud): Response
    {
        $this->authorize('view', $solicitud);

        return response($this->previewService->render($solicitud))
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
