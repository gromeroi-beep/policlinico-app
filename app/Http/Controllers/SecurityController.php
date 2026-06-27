<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

/**
 * SecurityController — Panel OWASP Real
 *
 * Gestiona el toggle global de seguridad y las vulnerabilidades
 * #3 OS Command Injection, #7 Security Misconfiguration, #10 Directory Traversal,
 * y sirve el panel de monitoreo OWASP con estado en tiempo real.
 *
 * NIST SP 800-53 Controls:
 *   SI-10 → Information Input Validation  (#3 Command, #10 Traversal)
 *   CM-7  → Least Functionality           (#3 Command, #7 Misconfig)
 *   CM-6  → Configuration Settings        (#7 Misconfig)
 *   AC-3  → Access Enforcement            (#10 Traversal, #6 Broken Access)
 *   AU-2  → Audit Events                  (todos los logs)
 */
class SecurityController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PANEL OWASP — Estado en tiempo real de las 10 vulnerabilidades
    | Acceso: solo admin (role:admin protegido en web.php)
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $modoSeguro = $this->getModoSeguro();

        // Mapa completo de las 10 vulnerabilidades con su estado OWASP y NIST
        $vulnerabilidades = [
            [
                'id'          => 1,
                'clave'       => 'vuln_sqli',
                'nombre'      => 'SQL Injection',
                'owasp'       => 'A03:2021',
                'nist'        => 'SI-10',
                'descripcion' => 'Consultas SQL construidas con concatenación de input del usuario.',
                'modulo'      => 'Login, Búsqueda de pacientes, Historial clínico',
                'riesgo'      => 'CRÍTICO',
            ],
            [
                'id'          => 2,
                'clave'       => 'vuln_xss',
                'nombre'      => 'Cross-Site Scripting (XSS)',
                'owasp'       => 'A03:2021',
                'nist'        => 'SI-10 / SC-18',
                'descripcion' => 'Reflejo de input del usuario sin escapar en las vistas Blade.',
                'modulo'      => 'Historial clínico, Citas, Usuarios',
                'riesgo'      => 'ALTO',
            ],
            [
                'id'          => 3,
                'clave'       => 'vuln_command',
                'nombre'      => 'OS Command Injection',
                'owasp'       => 'A03:2021',
                'nist'        => 'SI-10 / CM-7',
                'descripcion' => 'Ejecución de comandos del sistema operativo con input sin validar.',
                'modulo'      => 'Diagnóstico de red (/exec?cmd=)',
                'riesgo'      => 'CRÍTICO',
            ],
            [
                'id'          => 4,
                'clave'       => 'vuln_csrf',
                'nombre'      => 'CSRF',
                'owasp'       => 'A01:2021',
                'nist'        => 'SC-8',
                'descripcion' => 'Peticiones forjadas desde sitios externos ejecutadas con sesión activa.',
                'modulo'      => 'Todos los formularios POST del sistema',
                'riesgo'      => 'ALTO',
            ],
            [
                'id'          => 5,
                'clave'       => 'vuln_mass_assignment',
                'nombre'      => 'Mass Assignment',
                'owasp'       => 'A08:2021',
                'nist'        => 'SI-10',
                'descripcion' => 'Asignación masiva sin restricciones permite escalar privilegios vía HTTP.',
                'modulo'      => 'Registro de usuarios, Historial clínico',
                'riesgo'      => 'ALTO',
            ],
            [
                'id'          => 6,
                'clave'       => 'vuln_broken_access',
                'nombre'      => 'Broken Access Control',
                'owasp'       => 'A01:2021',
                'nist'        => 'AC-3',
                'descripcion' => 'Acceso a rutas de administrador cambiando la URL sin verificación de rol.',
                'modulo'      => 'Dashboard admin, Usuarios, Programaciones',
                'riesgo'      => 'CRÍTICO',
            ],
            [
                'id'          => 7,
                'clave'       => 'vuln_misconfig',
                'nombre'      => 'Security Misconfiguration',
                'owasp'       => 'A05:2021',
                'nist'        => 'CM-6 / CM-7',
                'descripcion' => 'APP_DEBUG=true expone stack traces, rutas y variables de entorno.',
                'modulo'      => 'Configuración global de la aplicación',
                'riesgo'      => 'MEDIO',
            ],
            [
                'id'          => 8,
                'clave'       => 'vuln_sensitive_data',
                'nombre'      => 'Sensitive Data Exposure',
                'owasp'       => 'A02:2021',
                'nist'        => 'SC-28 / IA-5',
                'descripcion' => 'Contraseñas almacenadas en texto plano y expuestas en respuestas JSON.',
                'modulo'      => 'Gestión de usuarios',
                'riesgo'      => 'CRÍTICO',
            ],
            [
                'id'          => 9,
                'clave'       => 'vuln_brute_force',
                'nombre'      => 'Brute Force / Rate Limiting',
                'owasp'       => 'A07:2021',
                'nist'        => 'AC-7',
                'descripcion' => 'Sin límite de intentos de login, permite ataque de fuerza bruta.',
                'modulo'      => 'Formulario de login',
                'riesgo'      => 'ALTO',
            ],
            [
                'id'          => 10,
                'clave'       => 'vuln_traversal',
                'nombre'      => 'Directory Traversal',
                'owasp'       => 'A01:2021',
                'nist'        => 'AC-3 / SI-10',
                'descripcion' => 'Acceso a archivos arbitrarios del servidor mediante manipulación de rutas.',
                'modulo'      => 'Descarga de archivos (/archivo?file=)',
                'riesgo'      => 'ALTO',
            ],
        ];

        // Últimos 50 eventos de seguridad para el log en tiempo real
        $logs = DB::table('security_logs')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Contadores para el resumen del panel
        $totalAtaques  = DB::table('security_logs')->count();
        $bloqueados    = DB::table('security_logs')->where('bloqueado', true)->count();
        $detectados    = DB::table('security_logs')->where('bloqueado', false)->count();

        // 🔥 FORZAR LECTURA ACTUALIZADA DE APP_DEBUG
        $appDebug = config('app.debug');
        
        // 🔥 TIMESTAMP PARA EVITAR CACHÉ
        $timestamp = time();

        return view('seguridad.index', compact(
            'modoSeguro',
            'vulnerabilidades',
            'logs',
            'totalAtaques',
            'bloqueados',
            'detectados',
            'appDebug',
            'timestamp'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE GLOBAL — Activa o desactiva todas las protecciones NIST
    |--------------------------------------------------------------------------
    */
    public function toggleModo(Request $request)
    {
        $modoActual = $this->getModoSeguro();
        $nuevoModo  = !$modoActual ? 1 : 0;
        $debugValue = ($nuevoModo === 0);

        // Actualizar el toggle global y todos los toggles individuales
        DB::table('security_settings')
            ->whereIn('clave', [
                'modo_seguro', 'vuln_sqli', 'vuln_xss', 'vuln_command',
                'vuln_csrf', 'vuln_mass_assignment', 'vuln_broken_access',
                'vuln_misconfig', 'vuln_sensitive_data',
                'vuln_brute_force', 'vuln_traversal',
            ])
            ->update(['valor' => $nuevoModo, 'updated_at' => now()]);

        // ===================================================================
        // VULNERABILIDAD #7 — Security Misconfiguration
        // NIST CM-6: Configuration Settings
        // ===================================================================
        // 🔥 ESCRIBIR EN EL ARCHIVO .env DIRECTAMENTE
        $this->setEnvValue('APP_DEBUG', $debugValue ? 'true' : 'false');
        
        // 🔥 FORZAR RECARGA DE CONFIGURACIÓN
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:cache');
        
        // 🔥 VOLVER A LEER LA CONFIGURACIÓN
        config(['app.debug' => $debugValue]);

        $this->registrarLog(
            'Toggle Global (CM-6)',
            'toggle_modo',
            sprintf(
                'Modo cambiado a: %s — Todas las protecciones NIST %s. APP_DEBUG=%s',
                $nuevoModo ? 'SEGURO' : 'INSEGURO',
                $nuevoModo ? 'ACTIVADAS' : 'DESACTIVADAS',
                $debugValue ? 'TRUE' : 'FALSE'
            ),
            $request,
            false
        );

        $mensaje = $nuevoModo
            ? '🟢 Modo SEGURO activado — Controles NIST SP 800-53 aplicados. OWASP ZAP no detectará vulnerabilidades.'
            : '🔴 Modo INSEGURO activado — Sistema expuesto. OWASP ZAP detectará las 10 vulnerabilidades.';

        return redirect()->route('seguridad.index', ['t' => time()])->with('toggle_resultado', $mensaje);
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE DEBUG — Solo APP_DEBUG (vulnerabilidad #7 individual)
    |--------------------------------------------------------------------------
    */
    public function toggleDebug(Request $request)
    {
        // Leer el estado actual desde la base de datos
        $valorActual = DB::table('security_settings')
            ->where('clave', 'vuln_misconfig')
            ->value('valor');
        
        // Invertir el valor (0→1, 1→0)
        $nuevoValor = $valorActual ? 0 : 1;
        $nuevoDebug = ($nuevoValor === 0);
        
        // 🔥 ESCRIBIR EN EL ARCHIVO .env
        $this->setEnvValue('APP_DEBUG', $nuevoDebug ? 'true' : 'false');
        
        // 🔥 LIMPIAR CACHÉ
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        
        // 🔥 FORZAR RECARGA DE CONFIGURACIÓN
        config(['app.debug' => $nuevoDebug]);
        
        // Actualizar en la base de datos
        DB::table('security_settings')
            ->where('clave', 'vuln_misconfig')
            ->update(['valor' => $nuevoValor, 'updated_at' => now()]);
        
        DB::table('security_settings')
            ->where('clave', 'modo_seguro')
            ->update(['valor' => $nuevoValor, 'updated_at' => now()]);
        
        $this->registrarLog(
            'Security Misconfiguration (CM-6)',
            'debug_toggle',
            'APP_DEBUG cambiado a: ' . ($nuevoDebug ? 'TRUE (vulnerable)' : 'FALSE (seguro)'),
            $request,
            false
        );
        
        // 🔥 REDIRECCIONAR CON TIMESTAMP PARA EVITAR CACHÉ
        return redirect()->route('seguridad.index', ['t' => time()])
            ->with('toggle_resultado', $nuevoDebug
                ? '⚠️ APP_DEBUG=true — Stack traces visibles. Provoca un error para ver la diferencia.'
                : '✅ APP_DEBUG=false — Errores genéricos. Sistema protegido.');
    }

    /*
    |--------------------------------------------------------------------------
    | DIRECTORY TRAVERSAL — Descarga de archivos
    | Vulnerabilidad #10
    |--------------------------------------------------------------------------
    */
    public function descargarArchivo(Request $request)
    {
        $archivo    = $request->query('file', '');
        $modoSeguro = $this->getModoSeguro();

        if (!$modoSeguro) {
            $rutaCompleta = base_path($archivo);

            $this->registrarLog(
                'Directory Traversal (AC-3)',
                'descarga_insegura',
                "Acceso a archivo sin validación: $archivo → $rutaCompleta",
                $request,
                false
            );

            if (!file_exists($rutaCompleta)) {
                return response()->json([
                    'error'   => 'Archivo no encontrado',
                    'ruta'    => $rutaCompleta,
                    'mensaje' => 'En modo inseguro: la ruta se construye directamente sin validación.',
                ], 404);
            }

            $contenido = file_get_contents($rutaCompleta);
            return response($contenido, 200)
                ->header('Content-Type', 'text/plain')
                ->header('X-Security-Warning', 'Directory Traversal vulnerability active');
        }

        if (
            str_contains($archivo, '..') ||
            str_contains($archivo, '/') ||
            str_contains($archivo, '\\') ||
            preg_match('/[^a-zA-Z0-9._-]/', $archivo)
        ) {
            $this->registrarLog(
                'Directory Traversal (AC-3)',
                'traversal_bloqueado',
                "Intento de Directory Traversal bloqueado. Archivo solicitado: $archivo",
                $request,
                true
            );

            abort(403, 'Acceso denegado — Ruta de archivo no permitida.');
        }

        $directorioSeguro = storage_path('app/public/descargas/');
        $rutaCompleta     = $directorioSeguro . basename($archivo);

        $this->registrarLog(
            'Directory Traversal (AC-3)',
            'descarga_segura',
            "Descarga segura: $archivo desde directorio protegido.",
            $request,
            false
        );

        if (!file_exists($rutaCompleta)) {
            abort(404, 'Archivo no encontrado.');
        }

        return response()->download($rutaCompleta);
    }

    /*
    |--------------------------------------------------------------------------
    | OS COMMAND INJECTION — Vulnerabilidad #3 (OWASP A03:2021)
    | NIST SI-10: Information Input Validation
    | NIST CM-7: Least Functionality
    |--------------------------------------------------------------------------
    */
    public function ejecutarComando(Request $request)
    {
        $comando    = $request->query('cmd', '');
        $modoSeguro = $this->getModoSeguro();

        if (!$modoSeguro) {
            $this->registrarLog(
                'OS Command Injection (SI-10)',
                'comando_ejecutado',
                "Comando ejecutado sin sanitizar: $comando",
                $request,
                false
            );

            try {
                $output = shell_exec($comando . ' 2>&1');
                return response()->json([
                    'modo'    => 'INSEGURO',
                    'comando' => $comando,
                    'output'  => $output ?: '(sin salida)',
                    'warning' => '⚠️ OS Command Injection activo - Este comando se ejecutó en el servidor real.'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        $this->registrarLog(
            'OS Command Injection (SI-10)',
            'comando_bloqueado',
            "Intento de ejecutar comando '$comando' bloqueado en modo seguro.",
            $request,
            true
        );

        return response()->json([
            'modo'    => 'SEGURO',
            'mensaje' => 'Los comandos del sistema están deshabilitados por seguridad (NIST CM-7).',
            'comando' => $comando
        ], 403);
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIG ADMIN — Ruta para demostrar Broken Access Control (#6)
    |--------------------------------------------------------------------------
    */
    public function configAdmin()
    {
        $modoSeguro = $this->getModoSeguro();

        $this->registrarLog(
            'Broken Access Control (AC-3)',
            'acceso_config_admin',
            $modoSeguro
                ? 'Acceso a config admin — rol verificado correctamente'
                : 'Acceso a config admin SIN verificación de rol (Broken Access Control activo)',
            request(),
            false
        );

        return view('seguridad.config_admin', compact('modoSeguro'));
    }

    /*
    |--------------------------------------------------------------------------
    | LOGS EN TIEMPO REAL — Últimos eventos de seguridad
    |--------------------------------------------------------------------------
    */
    public function logs()
    {
        $logs = DB::table('security_logs')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('seguridad.logs', compact('logs'));
    }

    /*
    |--------------------------------------------------------------------------
    | LIMPIAR LOGS DE SEGURIDAD
    |--------------------------------------------------------------------------
    */
    public function limpiarLogs(Request $request)
    {
        try {
            $total = DB::table('security_logs')->count();
            DB::table('security_logs')->truncate();
            Log::info('🧹 Logs de seguridad limpiados por: ' . auth()->user()->name);
            
            return redirect()->route('seguridad.index', ['t' => time()])->with('success', 
                '🧹 Logs de seguridad limpiados correctamente. (' . $total . ' registros eliminados)'
            );
            
        } catch (\Exception $e) {
            Log::error('❌ Error al limpiar logs: ' . $e->getMessage());
            return redirect()->route('seguridad.index')->with('error', 
                '❌ Error al limpiar los logs: ' . $e->getMessage()
            );
        }
    }

    // =======================================================================
    // HELPERS PRIVADOS
    // =======================================================================

    private function getModoSeguro(): bool
    {
        try {
            return (bool) DB::table('security_settings')
                ->where('clave', 'modo_seguro')
                ->value('valor');
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * 🔥 FUNCIÓN PARA MODIFICAR EL ARCHIVO .env
     */
    private function setEnvValue($key, $value)
    {
        $path = base_path('.env');
        
        if (!file_exists($path)) {
            return false;
        }
        
        // Leer el archivo .env
        $content = file_get_contents($path);
        
        // Buscar la línea con la clave
        $pattern = "/^" . preg_quote($key, '/') . "=.*/m";
        
        if (preg_match($pattern, $content)) {
            // Reemplazar la línea existente
            $content = preg_replace($pattern, $key . '=' . $value, $content);
        } else {
            // Agregar la línea al final
            $content .= PHP_EOL . $key . '=' . $value;
        }
        
        // Guardar el archivo
        return file_put_contents($path, $content) !== false;
    }

    private function registrarLog(
        string $vulnerabilidad,
        string $tipo,
        string $descripcion,
        $request,
        bool $bloqueado
    ): void {
        try {
            DB::table('security_logs')->insert([
                'vulnerabilidad' => $vulnerabilidad,
                'tipo'           => $tipo,
                'descripcion'    => $descripcion,
                'ip'             => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'usuario_id'     => auth()->id(),
                'bloqueado'      => $bloqueado,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('security_logs no disponible: ' . $e->getMessage());
        }
    }
}