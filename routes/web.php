<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\ProgramacionController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\SecurityModeController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\SecurityController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS - Sin autenticación
|--------------------------------------------------------------------------
*/

// Login
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

/*
|--------------------------------------------------------------------------
| RUTAS COMPARTIDAS - Admin y Médico autenticados
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // APIs internas compartidas (AJAX / Fetch API)
    Route::get('/api/medicos/{especialidad_id}', [ProgramacionController::class, 'getMedicos'])->name('api.medicos');
    Route::get('/api/paciente/{num_doc}', [CitaController::class, 'buscarPaciente'])->name('api.paciente');
    Route::get('/api/disponibilidad/{medico_id}', [CitaController::class, 'getDisponibilidad'])->name('api.disponibilidad');

    // -----------------------------------------------------------------------
    // VULNERABILIDAD #3: OS Command Injection
    // MODO INSEGURO  → ejecuta comandos del sistema
    // MODO SEGURO    → bloquea con 403
    // -----------------------------------------------------------------------
    Route::get('/exec', [SecurityController::class, 'ejecutarComando'])->name('exec');

    // -----------------------------------------------------------------------
    // VULNERABILIDAD #10: Directory Traversal
    // MODO INSEGURO  → ruta pública sin validación de path
    // MODO SEGURO    → SecurityController valida y restringe el path
    // -----------------------------------------------------------------------
    Route::get('/archivo', [SecurityController::class, 'descargarArchivo'])->name('archivo.descargar');
});

/*
|--------------------------------------------------------------------------
| RUTAS DEL ADMINISTRADOR - Solo role: admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {

    // Dashboard Admin
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Especialidades CRUD
    Route::resource('especialidades', EspecialidadController::class);

    // -----------------------------------------------------------------------
    // VULNERABILIDAD #5: Mass Assignment
    // MODO INSEGURO  → UserController::store() usa $request->all() sin filtrar
    // MODO SEGURO    → usa $request->only([campos permitidos])
    // VULNERABILIDAD #8: Sensitive Data Exposure
    // MODO INSEGURO  → passwords visibles en texto plano en la vista
    // MODO SEGURO    → passwords hasheadas, nunca expuestas
    // -----------------------------------------------------------------------
    Route::resource('usuarios', UserController::class);

    // Programaciones CRUD
    Route::resource('programaciones', ProgramacionController::class);

    // -----------------------------------------------------------------------
    // VULNERABILIDAD #4: CSRF
    // MODO INSEGURO  → rutas POST sin verificación de token CSRF
    // MODO SEGURO    → middleware VerifyCsrfToken activo (Laravel lo hace por defecto)
    // El toggle desactiva/activa VerifyCsrfToken según security_mode
    // -----------------------------------------------------------------------
    Route::resource('citas', CitaController::class);

    // Reportes Admin
    Route::get('/reportes', [CitaController::class, 'reportes'])->name('reportes.index');
    Route::get('/reportes/excel', [CitaController::class, 'exportarExcel'])->name('reportes.excel');
    Route::get('/reportes/pdf', [CitaController::class, 'exportarPdf'])->name('reportes.pdf');

    // Historial Clínico
    Route::get('/historiales', [HistorialController::class, 'index'])->name('historiales.index');
    Route::post('/historiales/buscar', [HistorialController::class, 'buscar'])->name('historiales.buscar');
    Route::post('/historiales', [HistorialController::class, 'store'])->name('historiales.store');
    Route::get('/historiales/{paciente}', [HistorialController::class, 'show'])->name('historiales.show');
    Route::get('/historiales/{paciente}/edit', [HistorialController::class, 'edit'])->name('historiales.edit');
    Route::put('/historiales/{paciente}', [HistorialController::class, 'update'])->name('historiales.update');
    Route::delete('/historiales/{paciente}', [HistorialController::class, 'destroy'])->name('historiales.destroy');

    // -----------------------------------------------------------------------
    // PANEL DE SEGURIDAD REAL - Reemplaza completamente el módulo Demo
    // Muestra el estado OWASP de las 10 vulnerabilidades en tiempo real
    // Toggle activa/desactiva protecciones NIST sobre el sistema real
    // -----------------------------------------------------------------------
    Route::get('/seguridad', [SecurityController::class, 'index'])->name('seguridad.index');
    Route::post('/seguridad/toggle', [SecurityController::class, 'toggleModo'])->name('seguridad.toggle');
    Route::get('/seguridad/logs', [SecurityController::class, 'logs'])->name('seguridad.logs');

    // -----------------------------------------------------------------------
    // LIMPIAR LOGS DE SEGURIDAD
    // -----------------------------------------------------------------------
    Route::delete('/seguridad/limpiar-logs', [SecurityController::class, 'limpiarLogs'])->name('seguridad.limpiar-logs');

    // -----------------------------------------------------------------------
    // VULNERABILIDAD #6: Broken Access Control
    // MODO INSEGURO  → cualquier URL de admin accesible cambiando la URL
    // MODO SEGURO    → CheckRole middleware bloquea (ya implementado abajo)
    // Esta ruta demuestra el bypass en modo inseguro:
    // -----------------------------------------------------------------------
    Route::get('/admin/config', [SecurityController::class, 'configAdmin'])->name('admin.config');

    // -----------------------------------------------------------------------
    // VULNERABILIDAD #7: Security Misconfiguration
    // MODO INSEGURO  → APP_DEBUG=true expone stack traces completos
    // MODO SEGURO    → APP_DEBUG=false, errores genéricos
    // Toggle lo maneja SecurityController modificando config en runtime
    // -----------------------------------------------------------------------
    Route::post('/seguridad/debug-toggle', [SecurityController::class, 'toggleDebug'])->name('seguridad.debug');
});

/*
|--------------------------------------------------------------------------
| RUTAS DEL MÉDICO - Solo role: medico
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:medico'])->group(function () {

    Route::get('/medico/dashboard', [MedicoController::class, 'dashboard'])->name('medico.dashboard');
    Route::get('/medico/citas', [MedicoController::class, 'citas'])->name('medico.citas');
    Route::put('/medico/citas/{cita}', [MedicoController::class, 'updateEstadoCita'])->name('medico.citas.update');
    
    // Historiales del médico - Rutas principales
    Route::get('/medico/historiales', [MedicoController::class, 'historiales'])->name('medico.historiales');
    Route::post('/medico/historiales/buscar', [MedicoController::class, 'buscarHistorial'])->name('medico.historiales.buscar');
    Route::post('/medico/historiales', [MedicoController::class, 'storeHistorial'])->name('medico.historiales.store');
    Route::get('/medico/historiales/{paciente}', [MedicoController::class, 'showHistorial'])->name('medico.historiales.show');
    
    // 🔥 NUEVAS RUTAS PARA EDITAR Y ELIMINAR HISTORIAL (MÉDICO)
    Route::get('/medico/historiales/{paciente}/edit', [MedicoController::class, 'editHistorial'])->name('medico.historiales.edit');
    Route::put('/medico/historiales/{paciente}', [MedicoController::class, 'updateHistorial'])->name('medico.historiales.update');
    Route::delete('/medico/historiales/{paciente}', [MedicoController::class, 'destroyHistorial'])->name('medico.historiales.destroy');
    
    // Reportes del médico
    Route::get('/medico/reportes', [MedicoController::class, 'reportes'])->name('medico.reportes');
    Route::get('/medico/reportes/excel', [MedicoController::class, 'exportarExcel'])->name('medico.reportes.excel');
    Route::get('/medico/reportes/pdf', [MedicoController::class, 'exportarPdf'])->name('medico.reportes.pdf');
});

// 🔥 Ruta para demostrar CSRF Attack
Route::get('/csrf-demo', function () {
    return view('csrf_attack');
});