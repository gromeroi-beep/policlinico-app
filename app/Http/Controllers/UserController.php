<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * NIST SP 800-53 Controls aplicados en este controlador:
 *
 *  SI-10 → Information Input Validation    (Mass Assignment protection)
 *  IA-5  → Authenticator Management        (passwords hasheadas)
 *  SC-28 → Protection of Information at Rest (Sensitive Data Exposure)
 *  AC-3  → Access Enforcement              (solo admin puede gestionar usuarios)
 */
class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAR USUARIOS
    | Vulnerabilidad #8: Sensitive Data Exposure
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $modoSeguro = $this->getModoSeguro();

        $usuarios = User::with('especialidad')
                        ->where('role', '!=', 'admin')
                        ->get();

        $especialidades = Especialidad::all();

        // ===================================================================
        // VULNERABILIDAD #8 — Sensitive Data Exposure
        // NIST SC-28: Protection of Information at Rest
        //
        // MODO INSEGURO → los passwords se pasan a la vista en texto plano
        //   (en la tabla se almacenaron sin hashear en modo inseguro)
        //   OWASP ZAP detectará: Sensitive Information Disclosure
        //
        // MODO SEGURO   → passwords siempre hasheadas (bcrypt), nunca
        //   expuestas en vistas ni en respuestas. La columna password
        //   está en $hidden del modelo User.
        // ===================================================================
        $this->registrarLog(
            'Sensitive Data Exposure (SC-28)',
            'listado_usuarios',
            $modoSeguro
                ? 'Listado de usuarios — passwords ocultas (modo seguro)'
                : 'Listado de usuarios — passwords potencialmente expuestas (modo inseguro)',
            request(),
            false
        );

        return view('usuarios.index', compact('usuarios', 'especialidades', 'modoSeguro'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREAR USUARIO
    | Vulnerabilidad #5: Mass Assignment · Vulnerabilidad #8: Sensitive Data
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $modoSeguro = $this->getModoSeguro();

        // Validación de campos — siempre activa independiente del modo
        $rules = [
            'name' => 'required|string|max:100',
            'role' => 'required|in:medico,paciente',
        ];

        if ($request->role === 'medico') {
            $rules['colegiatura']     = 'required|digits:5';
            $rules['especialidad_id'] = 'required|exists:especialidades,id';
            $rules['username']        = 'required|string|max:50|unique:users,username';
            $rules['password']        = 'required|string|min:6';
        }

        if ($request->role === 'paciente') {
            $rules['tipo_doc'] = 'required|in:DNI,CE';
            $rules['num_doc']  = $request->tipo_doc === 'DNI'
                ? 'required|digits:8|unique:users,num_doc'
                : 'required|digits:10|unique:users,num_doc';
        }

        $request->validate($rules);

        if (!$modoSeguro) {
            // ===============================================================
            // MODO INSEGURO — Mass Assignment REAL + Sensitive Data Exposure
            // NIST SI-10 DESACTIVADO
            //
            // 🔥 CORREGIDO: Usamos except() para excluir _token y _method
            // $request->all() pasaba TODOS los campos incluyendo _token
            // que no existe en la tabla users, causando error.
            // ===============================================================
            $datosInseguros = $request->except('_token', '_method');

            // Password en texto plano — VULNERABLE #8
            // (no se aplica Hash::make)

            $this->registrarLog(
                'Mass Assignment (SI-10) + Sensitive Data (SC-28)',
                'usuario_creado_inseguro',
                'Usuario creado con $request->except() — password en texto plano. Campos recibidos: ' . implode(', ', array_keys($datosInseguros)),
                $request,
                false
            );

            User::create($datosInseguros);

            return redirect()->route('usuarios.index')
                             ->with('success', '[MODO INSEGURO] Usuario creado — Mass Assignment activo.');
        }

        // ===================================================================
        // MODO SEGURO — Mass Assignment PROTEGIDO + Password Hasheada
        // NIST SI-10: solo campos explícitamente permitidos
        // NIST IA-5:  password siempre hasheada con bcrypt
        // ===================================================================
        $data = $request->only([
            'name', 'role', 'colegiatura',
            'especialidad_id', 'username',
            'tipo_doc', 'num_doc',
        ]);

        // NIST IA-5 — password hasheada, nunca en texto plano
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $this->registrarLog(
            'Mass Assignment (SI-10)',
            'usuario_creado_seguro',
            'Usuario creado con only() — password hasheada. Campos: ' . implode(', ', array_keys($data)),
            $request,
            false
        );

        User::create($data);

        return redirect()->route('usuarios.index')
                         ->with('success', 'Usuario registrado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR USUARIO
    |--------------------------------------------------------------------------
    */
    public function destroy(User $usuario)
    {
        $usuario->delete();
        return redirect()->route('usuarios.index')
                         ->with('success', 'Usuario eliminado correctamente.');
    }

    // =======================================================================
    // HELPERS PRIVADOS
    // =======================================================================

    /**
     * Lee el modo de seguridad global.
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
            return true;
        }
    }

    /**
     * Registra eventos en security_logs.
     * NIST AU-2 + AU-12
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