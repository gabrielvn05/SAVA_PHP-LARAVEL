@extends('layouts.app')

@section('title', 'Mis solicitudes')

@section('content')
<section class="stack">
    <header class="page-header row" style="justify-content: space-between; align-items: flex-end;">
        <div>
            <h1>Mis solicitudes</h1>
            <p class="field-hint">Historial de trámites registrados.</p>
        </div>
        <a href="{{ route('solicitudes.create') }}" class="btn btn--primary">Nueva solicitud</a>
    </header>

    <article class="card">
        @if($solicitudes->isEmpty())
            <p class="field-hint">No tienes solicitudes registradas.</p>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Periodo</th>
                            <th>Estado</th>
                            <th>Creada</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($solicitudes as $solicitud)
                            <tr>
                                <td>{{ $solicitud->tipo->label() }}</td>
                                <td>{{ $solicitud->fecha_inicio->format('d/m/Y') }} – {{ $solicitud->fecha_fin->format('d/m/Y') }}</td>
                                <td><span class="badge {{ $solicitud->estado->badgeClass() }}">{{ $solicitud->estado->label() }}</span></td>
                                <td>{{ $solicitud->created_at->format('d/m/Y H:i') }}</td>
                                <td><a href="{{ route('solicitudes.show', $solicitud) }}">Ver</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </article>
</section>
@endsection
