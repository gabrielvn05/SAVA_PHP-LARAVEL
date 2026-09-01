@extends('layouts.app')

@section('title', 'Perfil y configuración')

@section('content')
<section class="stack page-enter">
    <header class="page-header">
        <div class="page-header__text">
            <h1 class="page-header__title">Perfil y configuración</h1>
            <p class="page-header__subtitle">Actualiza tu cédula, carrera y celular. El nombre y el correo provienen de tu cuenta institucional.</p>
        </div>
    </header>

    <article class="card stack" style="max-width: 36rem;">
        <h2 style="margin: 0; font-size: 1.1rem;">Cuenta</h2>
        <div class="form-grid form-grid--2" style="gap: 0.35rem 1rem;">
            <div>
                <span class="field-hint">Nombre</span>
                <div>{{ $user->nombreCompleto() }}</div>
            </div>
            <div>
                <span class="field-hint">Correo</span>
                <div>{{ $user->email }}</div>
            </div>
            <div>
                <span class="field-hint">Rol</span>
                <div>{{ $user->rol->label() }}</div>
            </div>
        </div>
    </article>

    <article class="card stack" style="max-width: 36rem;">
        <h2 style="margin: 0; font-size: 1.1rem;">Datos institucionales</h2>
        <p class="field-hint" style="margin: 0;">Estos datos se usan en las solicitudes y oficios.</p>

        @if($errors->any())
            <div class="alert alert--error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('perfil.update') }}" class="stack" data-loading-label="Guardando perfil…">
            @csrf
            @method('PUT')
            @include('perfil._campos')
            <div class="row" style="justify-content: flex-end;">
                <button type="submit" class="btn btn--primary">Guardar cambios</button>
            </div>
        </form>
    </article>
</section>
@endsection
