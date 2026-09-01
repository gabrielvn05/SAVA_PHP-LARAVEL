@extends('layouts.app')

@section('title', 'Superusuario')

@section('content')
<section class="stack">
    <header class="page-header">
        <div class="page-header__text">
            <h1 class="page-header__title">Superusuario</h1>
            <p class="page-header__subtitle">Cuenta de respaldo sin módulos operativos visibles.</p>
        </div>
    </header>
    <article class="card">
        <p class="field-hint" style="margin: 0;">
            Este perfil se mantiene disponible solo para contingencias. Usa el menú lateral para gestionar usuarios y solicitudes de cuenta.
        </p>
    </article>
</section>
@endsection
