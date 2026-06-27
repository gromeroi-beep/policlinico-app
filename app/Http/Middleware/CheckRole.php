<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * NIST SP 800-53 Control: AC-3 (Access Enforcement)
 *
 * Vulnerabilidad #6 — Broken Access Control
 *
 * MODO INSEGURO : el middleware ignora el rol → cualquier usuario
 *                 autenticado accede a rutas de admin solo cambiando la URL.
 *
 * MODO SEGURO   : verifica el rol estrictamente y registra cada intento
 *                 de acceso no autorizado en security_logs.
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Verificar autenticación básica (siempre activa)
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // ---------------------------------------------------------------
        // Leer modo de seguridad desde la base de datos
        // La tabla security_settings tiene una fila con:
        //   modo_seguro TINYINT(1)  → 0 = inseguro, 1 = seguro
        // ---------------------------------------------------------------
        $modoSeguro = $this->getModoSeguro();

        if (!$modoSeguro) {
            // ===========================================================
            // MODO INSEGURO — NIST AC-3 DESACTIVADO
            // Broken Access Control: NO se verifica el rol
            // Un médico puede acceder a /dashboard, /usuarios, etc.
            // OWASP ZAP detectará: Missing Access Control
            // ===========================================================
            return $next($request);
        }

        // ===========================================================
        // MODO SEGURO — NIST AC-3 ACTIVADO
        // Se verifica el rol del usuario autenticado
        // ===========================================================
        if (!in_array(auth()->user()->role, $roles)) {

            // Registrar intento de acceso no autorizado (NIST AU-2: Audit Events)
            $this->registrarIntento($request, $roles);

            abort(403, 'Acceso denegado — No tiene permisos para esta sección.');
        }

        return $next($request);
    }

    /**
     * Lee el modo de seguridad desde la base de datos.
     * Usa cache de sesión para no hacer query en cada request.
     */
    private function getModoSeguro(): bool
    {
        try {
            $setting = DB::table('security_settings')
                ->where('clave', 'modo_seguro')
                ->value('valor');

            return (bool) $setting;
        } catch (\Exception $e) {
            // Si la tabla no existe aún, asume modo seguro por defecto
            return true;
        }
    }

    /**
     * Registra el intento de acceso no autorizado.
     * NIST AU-2: Auditable Events / AU-12: Audit Record Generation
     */
    private function registrarIntento(Request $request, array $rolesRequeridos): void
    {
        try {
            DB::table('security_logs')->insert([
                'vulnerabilidad'  => 'Broken Access Control (AC-3)',
                'tipo'            => 'acceso_no_autorizado',
                'descripcion'     => sprintf(
                    'Usuario "%s" (rol: %s) intentó acceder a ruta restringida para [%s]: %s',
                    auth()->user()->name,
                    auth()->user()->role,
                    implode(', ', $rolesRequeridos),
                    $request->path()
                ),
                'ip'              => $request->ip(),
                'user_agent'      => $request->userAgent(),
                'usuario_id'      => auth()->id(),
                'bloqueado'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        } catch (\Exception $e) {
            // Silencioso si la tabla aún no existe
            Log::warning('security_logs no disponible: ' . $e->getMessage());
        }
    }
}