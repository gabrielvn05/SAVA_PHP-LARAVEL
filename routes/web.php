<?php

use App\Http\Controllers\Admin\AccountRequestController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MicrosoftAuthController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\CertificadoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SolicitarCuentaController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\SolicitudProcesoController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/auth/microsoft', [MicrosoftAuthController::class, 'redirect'])->name('auth.microsoft');
    Route::get('/auth/microsoft/callback', [MicrosoftAuthController::class, 'callback'])->name('auth.microsoft.callback');

    Route::get('/solicitar-cuenta', [SolicitarCuentaController::class, 'create'])->name('solicitar-cuenta.create');
    Route::post('/solicitar-cuenta', [SolicitarCuentaController::class, 'store'])->name('solicitar-cuenta.store');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/cambiar-clave', [PasswordChangeController::class, 'edit'])->name('cambiar-clave.edit');
    Route::put('/cambiar-clave', [PasswordChangeController::class, 'update'])->name('cambiar-clave.update');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [MicrosoftAuthController::class, 'logout'])->name('logout');

    Route::get('/solicitudes/nueva/wizard', [SolicitudController::class, 'wizard'])->name('solicitudes.wizard');
    Route::resource('solicitudes', SolicitudController::class)->except(['index']);
    Route::get('/solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');

    Route::get('/solicitudes/{solicitud}/preview-oficio', [CertificadoController::class, 'preview'])
        ->name('solicitudes.preview-oficio');

    Route::get('/solicitudes/proceso-aprobacion', [SolicitudProcesoController::class, 'index'])
        ->name('solicitudes.proceso');
    Route::post('/solicitudes/{solicitud}/revisar', [SolicitudProcesoController::class, 'revisar'])
        ->name('solicitudes.revisar');
    Route::post('/solicitudes/{solicitud}/aprobar', [SolicitudProcesoController::class, 'aprobar'])
        ->name('solicitudes.aprobar');

    Route::get('/secretaria/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/secretaria/reportes/export', [ReporteController::class, 'export'])->name('reportes.export');

    Route::get('/admin/usuarios', [UsuarioController::class, 'index'])->name('admin.usuarios.index');
    Route::post('/admin/usuarios', [UsuarioController::class, 'store'])->name('admin.usuarios.store');
    Route::patch('/admin/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('admin.usuarios.update');
    Route::post('/admin/usuarios/{usuario}/delegar', [UsuarioController::class, 'delegate'])
        ->name('admin.usuarios.delegate');

    Route::get('/admin/solicitudes-cuenta', [AccountRequestController::class, 'index'])
        ->name('admin.solicitudes-cuenta.index');
    Route::post('/admin/solicitudes-cuenta/{accountRequest}/aprobar', [AccountRequestController::class, 'aprobar'])
        ->name('admin.solicitudes-cuenta.aprobar');
    Route::post('/admin/solicitudes-cuenta/{accountRequest}/rechazar', [AccountRequestController::class, 'rechazar'])
        ->name('admin.solicitudes-cuenta.rechazar');
});
