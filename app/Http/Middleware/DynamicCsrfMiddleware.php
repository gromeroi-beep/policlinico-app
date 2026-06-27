<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DynamicCsrfMiddleware extends \App\Http\Middleware\VerifyCsrfToken
{
    protected $except = [];

    public function handle($request, Closure $next)
    {
        // 🔥 LOG MUY DETALLADO
        Log::info('========================================');
        Log::info('🔍 CSRF MIDDLEWARE EJECUTADO');
        Log::info('🔍 Ruta: ' . $request->path());
        Log::info('🔍 Método: ' . $request->method());
        Log::info('🔍 IP: ' . $request->ip());
        
        // 🔥 LEER MODO DE SEGURIDAD DESDE LA BASE DE DATOS
        try {
            $valor = DB::table('security_settings')
                ->where('clave', 'modo_seguro')
                ->value('valor');
            $modoSeguro = (bool) $valor;
            Log::info('🔍 Modo seguro (desde DB): ' . ($modoSeguro ? 'SI' : 'NO'));
            Log::info('🔍 Valor en DB: ' . ($valor ?? 'null'));
        } catch (\Exception $e) {
            Log::info('🔍 Error al leer DB: ' . $e->getMessage());
            $modoSeguro = true;
        }

        // 🔥 VERIFICAR EL TOKEN CSRF
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
        Log::info('🔍 Token CSRF recibido: ' . ($token ? 'SI ('.substr($token,0,20).'...)' : 'NO'));

        // 🔴 MODO INSEGURO → CSRF DESACTIVADO
        if (!$modoSeguro) {
            Log::info('🔴 CSRF DESACTIVADO (modo inseguro) - PASANDO PETICIÓN SIN VERIFICAR');
            $this->registrarLog($request, false);
            Log::info('========================================');
            return $next($request);
        }

        // 🟢 MODO SEGURO → CSRF ACTIVADO
        Log::info('🟢 CSRF ACTIVADO (modo seguro) - VERIFICANDO TOKEN');
        $this->registrarLog($request, true);

        if (!in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            Log::info('⏩ No es POST/PUT/DELETE, pasando sin verificar');
            Log::info('========================================');
            return $next($request);
        }

        if (!$token) {
            Log::warning('❌ CSRF token no encontrado - ABORTANDO CON 419');
            Log::info('========================================');
            abort(419, 'CSRF token no encontrado.');
        }

        $sessionToken = $request->session()->token();
        Log::info('🔍 Token de sesión: ' . ($sessionToken ? 'SI' : 'NO'));
        
        if (!$sessionToken || !hash_equals($sessionToken, $token)) {
            Log::warning('❌ CSRF token inválido - ABORTANDO CON 419');
            Log::info('========================================');
            abort(419, 'CSRF token mismatch.');
        }

        Log::info('✅ CSRF token verificado correctamente');
        Log::info('========================================');
        return $next($request);
    }

    private function registrarLog(Request $request, bool $modoSeguro): void
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return;
        }

        if (in_array($request->path(), ['login', 'logout', '/login', '/logout'])) {
            return;
        }

        try {
            DB::table('security_logs')->insert([
                'vulnerabilidad' => 'CSRF (SC-8)',
                'tipo'           => $modoSeguro ? 'csrf_verificado' : 'csrf_omitido',
                'descripcion'    => sprintf(
                    '%s /%s — Token CSRF %s',
                    $request->method(),
                    $request->path(),
                    $modoSeguro ? 'VERIFICADO' : 'NO verificado (vulnerable)'
                ),
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'usuario_id' => auth()->id(),
                'bloqueado'  => $modoSeguro,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('security_logs no disponible: ' . $e->getMessage());
        }
    }
}