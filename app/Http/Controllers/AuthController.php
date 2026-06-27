<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * NIST SP 800-53 Controls aplicados en este controlador:
 *
 *  IA-5  → Authenticator Management        (passwords hasheadas)
 *  AC-7  → Unsuccessful Logon Attempts     (Rate Limiting / Brute Force)
 *  SC-8  → Transmission Confidentiality    (HTTPS en producción)
 *  SI-10 → Information Input Validation    (Prepared Statements vs SQLi)
 *  AU-2  → Audit Events                    (registro de intentos fallidos)
 */
class AuthController extends Controller
{
    // -----------------------------------------------------------------------
    // Límite de intentos de login (NIST AC-7)
    // -----------------------------------------------------------------------
    private const MAX_INTENTOS   = 5;
    private const BLOQUEO_MINUTOS = 10;

    /*
    |--------------------------------------------------------------------------
    | MOSTRAR FORMULARIO DE LOGIN
    |--------------------------------------------------------------------------
    */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirigirPorRol(Auth::user()->role);
        }
        return view('auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | PROCESAR LOGIN
    | Vulnerabilidades togglables: #1 SQLi · #4 CSRF · #9 Brute Force
    |--------------------------------------------------------------------------
    */
    public function login(Request $request)
    {
        // ===================================================================
        // 🔴 LEER EL MODO DESDE LA BASE DE DATOS
        // ===================================================================
        $modoSeguro = $this->getModoSeguro();
        
        // 🔍 LOG DE DEPURACIÓN
        Log::info('🔴 MODO DE SEGURIDAD (desde DB): ' . ($modoSeguro ? 'SEGURO' : 'INSEGURO'));
        Log::info('📝 Username recibido: ' . $request->input('username'));

        // ===================================================================
        // VULNERABILIDAD #9 — Brute Force / Rate Limiting
        // NIST AC-7: Unsuccessful Logon Attempts
        //
        // MODO INSEGURO → sin límite de intentos, se puede atacar con
        //                 herramientas como Hydra o Burp Suite Intruder
        // MODO SEGURO   → bloquea la IP por 10 min tras 5 intentos fallidos
        // ===================================================================
        if ($modoSeguro) {
            $bloqueo = $this->verificarBloqueo($request);
            if ($bloqueo) {
                $this->registrarLog(
                    'Brute Force / Rate Limiting (AC-7)',
                    'ip_bloqueada',
                    'IP bloqueada por exceder intentos de login: ' . $request->ip(),
                    $request,
                    true
                );
                return back()->withErrors([
                    'login' => 'Demasiados intentos fallidos. Intente en ' . self::BLOQUEO_MINUTOS . ' minutos.',
                ]);
            }
        }
        // MODO INSEGURO: sin verificación de bloqueo → siguiente línea no existe

        // Validación básica de campos (siempre activa)
        $request->validate([
            'username' => 'required|string|max:100',
            'password' => 'required|string|min:1',
        ]);

        $username = trim($request->input('username'));
        $password = $request->input('password');

        // ===================================================================
        // VULNERABILIDAD #1 — SQL Injection
        // NIST SI-10: Information Input Validation
        //
        // MODO INSEGURO → concatenación directa del input en la consulta SQL
        //   Payload de ataque en username: admin' OR '1'='1' --
        //   Resultado: accede sin contraseña, bypass total del login
        //
        // MODO SEGURO   → Auth::attempt() usa Eloquent con Prepared Statements
        //   El input es tratado como dato, nunca como código SQL
        // ===================================================================
        if (!$modoSeguro) {

            // ---------------------------------------------------------------
            // MODO INSEGURO — SQLi REAL usando DB::raw() para evitar escapes
            // ---------------------------------------------------------------
            Log::info('✅ ENTRANDO AL BLOQUE SQLi - Consulta vulnerable ejecutada');
            
            try {
                // Usamos DB::raw() para evitar que Laravel escape la consulta
                $resultado = DB::select(
                    DB::raw("SELECT * FROM users WHERE username = '$username' AND role IN ('admin','medico') LIMIT 1")
                );
                Log::info('📊 Resultado de la consulta: ' . json_encode($resultado));
            } catch (\Exception $e) {
                Log::error('❌ Error en SQLi: ' . $e->getMessage());
                $resultado = [];
            }

            $this->registrarLog(
                'SQL Injection (SI-10)',
                'login_inseguro',
                "Consulta SQL sin sanitizar ejecutada. Username: $username",
                $request,
                false
            );

            if (!empty($resultado)) {
                Log::info('✅ USUARIO ENCONTRADO - Login exitoso por SQLi');
                $user = \App\Models\User::find($resultado[0]->id);
                Auth::login($user);
                $request->session()->regenerate();
                return $this->redirigirPorRol($user->role);
            }

            Log::warning('❌ No se encontró usuario con SQLi');
            return back()->withErrors([
                'login' => 'Credenciales incorrectas.',
            ])->onlyInput('username');
        }

        // ===================================================================
        // MODO SEGURO — SQLi PROTEGIDO (NIST SI-10 + IA-5)
        // Auth::attempt() usa Eloquent → Prepared Statements automáticos
        // Hash::check() verifica el password hasheado — nunca texto plano
        // ===================================================================
        $credentials = [
            'username' => $username,
            'password' => $password,
        ];

        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            if (!in_array($user->role, ['admin', 'medico'])) {
                Auth::logout();
                return back()->withErrors([
                    'login' => 'No tiene permisos para acceder al sistema.',
                ])->onlyInput('username');
            }

            // Sesión regenerada → previene Session Fixation (NIST SC-23)
            $request->session()->regenerate();

            // Limpiar contador de intentos fallidos al login exitoso
            $this->limpiarIntentos($request);

            $this->registrarLog(
                'Login exitoso (SI-10 + AC-7)',
                'login_exitoso',
                "Login seguro. Usuario: $username",
                $request,
                false
            );

            return $this->redirigirPorRol($user->role);
        }

        // Login fallido — registrar intento para Brute Force detection
        if ($modoSeguro) {
            $this->registrarIntento($request);
        }

        $this->registrarLog(
            'SQL Injection (SI-10)',
            'login_fallido',
            "Intento fallido. Username: $username",
            $request,
            false
        );

        // Mensaje genérico — no revela si el usuario existe (NIST SC-28)
        return back()->withErrors([
            'login' => 'Credenciales incorrectas. Intente nuevamente.',
        ])->onlyInput('username');
    }

    /*
    |--------------------------------------------------------------------------
    | REDIRECCIÓN POR ROL (RBAC - NIST AC-2)
    |--------------------------------------------------------------------------
    */
    private function redirigirPorRol(string $role)
    {
        return match($role) {
            'admin'  => redirect()->route('dashboard'),
            'medico' => redirect()->route('medico.dashboard'),
            default  => redirect()->route('login'),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT SEGURO (NIST AC-12: Session Termination)
    |--------------------------------------------------------------------------
    */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();   // Invalida sesión completa
        $request->session()->regenerateToken(); // Nuevo token CSRF
        return redirect()->route('login');
    }

    // =======================================================================
    // HELPERS PRIVADOS
    // =======================================================================

    /**
     * Lee el modo de seguridad global desde la base de datos.
     * NIST CM-6: Configuration Settings
     */
    private function getModoSeguro(): bool
    {
        try {
            $valor = DB::table('security_settings')
                ->where('clave', 'modo_seguro')
                ->value('valor');
            return (bool) $valor;
        } catch (\Exception $e) {
            return true; // Por defecto: seguro
        }
    }

    /**
     * Verifica si la IP está bloqueada por exceso de intentos.
     * NIST AC-7: Unsuccessful Logon Attempts
     */
    private function verificarBloqueo(Request $request): bool
    {
        $clave = 'login_intentos_' . $request->ip();
        $intentos = Cache::get($clave, 0);
        return $intentos >= self::MAX_INTENTOS;
    }

    /**
     * Registra un intento fallido de login.
     */
    private function registrarIntento(Request $request): void
    {
        $clave = 'login_intentos_' . $request->ip();
        $intentos = Cache::get($clave, 0) + 1;
        Cache::put($clave, $intentos, now()->addMinutes(self::BLOQUEO_MINUTOS));
    }

    /**
     * Limpia los intentos fallidos tras login exitoso.
     */
    private function limpiarIntentos(Request $request): void
    {
        Cache::forget('login_intentos_' . $request->ip());
    }

    /**
     * Registra eventos en security_logs.
     * NIST AU-2: Audit Events / AU-12: Audit Record Generation
     */
    private function registrarLog(
        string $vulnerabilidad,
        string $tipo,
        string $descripcion,
        Request $request,
        bool $bloqueado
    ): void {
        try {
            DB::table('security_logs')->insert([
                'vulnerabilidad' => $vulnerabilidad,
                'tipo'           => $tipo,
                'descripcion'    => $descripcion,
                'ip'             => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'usuario_id'     => null,
                'bloqueado'      => $bloqueado,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('security_logs no disponible: ' . $e->getMessage());
        }
    }
}