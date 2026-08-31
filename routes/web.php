<?php

use App\Http\Controllers\Admin\AccountRequestController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Auth\MicrosoftAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SolicitarCuentaController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\SolicitudProcesoController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function (): void {
    Route::get('/login', fn () => view('auth.login'))->name('login');
    Route::get('/auth/microsoft', [MicrosoftAuthController::class, 'redirect'])->name('auth.microsoft');
    Route::get('/auth/microsoft/callback', [MicrosoftAuthController::class, 'callback'])->name('auth.microsoft.callback');

    Route::get('/solicitar-cuenta', [SolicitarCuentaController::class, 'create'])->name('solicitar-cuenta.create');
    Route::post('/solicitar-cuenta', [SolicitarCuentaController::class, 'store'])->name('solicitar-cuenta.store');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [MicrosoftAuthController::class, 'logout'])->name('logout');

    Route::resource('solicitudes', SolicitudController::class)->except(['index']);
    Route::get('/solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');

    Route::get('/solicitudes/proceso-aprobacion', [SolicitudProcesoController::class, 'index'])
        ->name('solicitudes.proceso');
    Route::post('/solicitudes/{solicitud}/revisar', [SolicitudProcesoController::class, 'revisar'])
        ->name('solicitudes.revisar');
    Route::post('/solicitudes/{solicitud}/aprobar', [SolicitudProcesoController::class, 'aprobar'])
        ->name('solicitudes.aprobar');

    Route::get('/admin/usuarios', [UsuarioController::class, 'index'])->name('admin.usuarios.index');
    Route::patch('/admin/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('admin.usuarios.update');

    Route::get('/admin/solicitudes-cuenta', [AccountRequestController::class, 'index'])
        ->name('admin.solicitudes-cuenta.index');
    Route::post('/admin/solicitudes-cuenta/{accountRequest}/aprobar', [AccountRequestController::class, 'aprobar'])
        ->name('admin.solicitudes-cuenta.aprobar');
    Route::post('/admin/solicitudes-cuenta/{accountRequest}/rechazar', [AccountRequestController::class, 'rechazar'])
        ->name('admin.solicitudes-cuenta.rechazar');
});
