<?php

namespace App\Http\Controllers;

use App\Models\HistorialClinico;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * NIST SP 800-53 Controls aplicados en este controlador:
 *
 *  SI-10 → Information Input Validation  (SQLi en búsqueda — #1)
 *  AC-3  → Access Enforcement            (Directory Traversal — #10)
 *  AU-2  → Audit Events                  (registro de accesos a archivos)
 */
class HistorialController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAR PACIENTES CON HISTORIAL
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $pacientes  = User::where('role', 'paciente')->get();
        $modoSeguro = $this->getModoSeguro();
        return view('historiales.index', compact('pacientes', 'modoSeguro'));
    }

    /*
    |--------------------------------------------------------------------------
    | BUSCAR HISTORIAL POR DOCUMENTO
    | Vulnerabilidad #1: SQL Injection (togglable)
    |--------------------------------------------------------------------------
    */
    public function buscar(Request $request)
    {
        $request->validate([
            'num_doc' => 'required|string',
        ]);

        $num_doc    = $request->input('num_doc');
        $modoSeguro = $this->getModoSeguro();

        if (!$modoSeguro) {
            try {
                $resultado = DB::select(
                    "SELECT * FROM users
                     WHERE num_doc = '$num_doc' AND role = 'paciente' LIMIT 1"
                );
            } catch (\Exception $e) {
                $resultado = [];
            }

            $this->registrarLog(
                'SQL Injection (SI-10)',
                'busqueda_historial_insegura',
                "Búsqueda de historial con concatenación SQL. num_doc: $num_doc",
                $request,
                false
            );

            if (empty($resultado)) {
                return back()->withErrors([
                    'num_doc' => 'No se encontró ningún paciente con ese documento.'
                ])->withInput();
            }

            $paciente = User::find($resultado[0]->id);
            $historial = HistorialClinico::where('paciente_id', $paciente->id)->first();
            return view('historiales.show', compact('paciente', 'historial'));
        }

        $paciente = User::where('num_doc', $num_doc)
                        ->where('role', 'paciente')
                        ->first();

        $this->registrarLog(
            'SQL Injection (SI-10)',
            'busqueda_historial_segura',
            "Búsqueda con Eloquent prepared statement. num_doc: $num_doc",
            $request,
            false
        );

        if (!$paciente) {
            return back()->withErrors([
                'num_doc' => 'No se encontró ningún paciente con ese documento.'
            ])->withInput();
        }

        $historial = HistorialClinico::where('paciente_id', $paciente->id)->first();
        return view('historiales.show', compact('paciente', 'historial'));
    }

    /*
    |--------------------------------------------------------------------------
    | MOSTRAR HISTORIAL DE UN PACIENTE
    | Vulnerabilidad #6: Broken Access Control (via URL directa)
    |--------------------------------------------------------------------------
    */
    public function show($paciente_id)
    {
        $modoSeguro = $this->getModoSeguro();

        if (!$modoSeguro) {
            $paciente  = User::findOrFail($paciente_id);
            $historial = HistorialClinico::where('paciente_id', $paciente_id)->first();

            $this->registrarLog(
                'Broken Access Control (AC-3)',
                'historial_acceso_directo',
                "Acceso directo a historial sin verificación de permisos. paciente_id: $paciente_id",
                request(),
                false
            );

            return view('historiales.show', compact('paciente', 'historial'));
        }

        $user = auth()->user();

        if ($user->role === 'admin') {
            $paciente = User::findOrFail($paciente_id);
        } else {
            $tieneCita = DB::table('citas')
                ->where('paciente_id', $paciente_id)
                ->where('medico_id', $user->id)
                ->exists();

            if (!$tieneCita) {
                $this->registrarLog(
                    'Broken Access Control (AC-3)',
                    'acceso_bloqueado',
                    "Médico $user->id intentó ver historial de paciente $paciente_id sin cita. Bloqueado.",
                    request(),
                    true
                );
                abort(403, 'No tiene permiso para ver el historial de este paciente.');
            }

            $paciente = User::findOrFail($paciente_id);
        }

        $historial = HistorialClinico::where('paciente_id', $paciente->id)->first();
        return view('historiales.show', compact('paciente', 'historial'));
    }

    /*
    |--------------------------------------------------------------------------
    | EDITAR HISTORIAL
    |--------------------------------------------------------------------------
    */
    public function edit($paciente_id)
    {
        $paciente = User::findOrFail($paciente_id);
        $historial = HistorialClinico::where('paciente_id', $paciente_id)->first();
        
        if (!$historial) {
            return redirect()->route('historiales.index')
                ->with('error', 'Este paciente no tiene historial clínico.');
        }
        
        return view('historiales.edit', compact('paciente', 'historial'));
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR HISTORIAL
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $paciente_id)
    {
        $request->validate([
            'grupo_sanguineo' => 'nullable|string|max:5',
            'alergias' => 'nullable|string',
            'diagnostico_principal' => 'nullable|string',
            'antecedentes_medicos' => 'nullable|string',
            'estado_paciente' => 'required|in:Estable,En Tratamiento,Crítico',
            'observaciones' => 'nullable|string',
        ]);

        $historial = HistorialClinico::where('paciente_id', $paciente_id)->first();
        
        if (!$historial) {
            return redirect()->route('historiales.index')
                ->with('error', 'No se encontró el historial clínico.');
        }

        $historial->update([
            'grupo_sanguineo' => $request->grupo_sanguineo,
            'alergias' => $request->alergias,
            'diagnostico_principal' => $request->diagnostico_principal,
            'antecedentes_medicos' => $request->antecedentes_medicos,
            'estado_paciente' => $request->estado_paciente,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('historiales.show', $paciente_id)
                         ->with('success', 'Historial clínico actualizado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR HISTORIAL
    |--------------------------------------------------------------------------
    */
    public function destroy($paciente_id)
    {
        $historial = HistorialClinico::where('paciente_id', $paciente_id)->first();
        
        if (!$historial) {
            return redirect()->route('historiales.index')
                ->with('error', 'No se encontró el historial clínico.');
        }

        $historial->delete();

        return redirect()->route('historiales.index')
                         ->with('success', 'Historial clínico eliminado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | CREAR HISTORIAL
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'paciente_id'           => 'required|exists:users,id',
            'grupo_sanguineo'       => 'nullable|string|max:5',
            'alergias'              => 'nullable|string',
            'diagnostico_principal' => 'nullable|string',
            'antecedentes_medicos'  => 'nullable|string',
            'estado_paciente'       => 'required|in:Estable,En Tratamiento,Crítico',
            'observaciones'         => 'nullable|string',
        ]);

        $codigo = 'HC-' . str_pad(
            HistorialClinico::count() + 1, 5, '0', STR_PAD_LEFT
        );

        HistorialClinico::updateOrCreate(
            ['paciente_id' => $request->paciente_id],
            [
                'codigo_historial'      => $codigo,
                'grupo_sanguineo'       => $request->grupo_sanguineo,
                'alergias'              => $request->alergias,
                'diagnostico_principal' => $request->diagnostico_principal,
                'antecedentes_medicos'  => $request->antecedentes_medicos,
                'estado_paciente'       => $request->estado_paciente,
                'observaciones'         => $request->observaciones,
            ]
        );

        return redirect()->route('historiales.index')
                         ->with('success', 'Historial clínico guardado correctamente.');
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