<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Programacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * NIST SP 800-53 Controls aplicados en este controlador:
 *
 *  SC-8  → Transmission Confidentiality / Integrity  (CSRF — #4)
 *  SI-10 → Information Input Validation              (SQLi en búsqueda — #1)
 *  AC-3  → Access Enforcement                        (solo admin gestiona citas)
 */
class CitaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAR CITAS
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $citas          = Cita::with(['paciente', 'especialidad', 'medico'])->get();
        $especialidades = Especialidad::all();
        $modoSeguro     = $this->getModoSeguro();

        return view('citas.index', compact('citas', 'especialidades', 'modoSeguro'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREAR CITA
    | Vulnerabilidad #4: CSRF
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        // ===================================================================
        // VULNERABILIDAD #4 — CSRF (Cross-Site Request Forgery)
        // NIST SC-8: Transmission Confidentiality and Integrity
        //
        // MODO INSEGURO → el middleware VerifyCsrfToken está desactivado
        //   Un atacante puede crear una página maliciosa que envíe un
        //   formulario POST a /citas con los datos de una cita falsa.
        //   Si el admin tiene sesión activa, la cita se registra sin saberlo.
        //   OWASP ZAP detectará: Absence of Anti-CSRF Tokens
        //
        // MODO SEGURO  → VerifyCsrfToken activo (Laravel lo incluye por defecto)
        //   El token @csrf en el formulario valida que la petición
        //   viene del propio sistema, no de un sitio externo.
        //
        // El toggle se maneja en app/Http/Middleware/Kernel.php
        // desactivando VerifyCsrfToken del grupo 'web' en modo inseguro.
        // El registro del evento ocurre aquí para el panel OWASP.
        // ===================================================================
        $modoSeguro = $this->getModoSeguro();

        $this->registrarLog(
            'CSRF (SC-8)',
            'cita_store',
            $modoSeguro
                ? 'POST /citas con token CSRF validado — protegido'
                : 'POST /citas SIN validación CSRF — vulnerable a peticiones cruzadas',
            $request,
            false
        );

        $request->validate([
            'especialidad_id' => 'required|exists:especialidades,id',
            'medico_id'       => 'required|exists:users,id',
            'fecha_cita'      => 'required|date',
            'hora_cita'       => 'required',
            'paciente_nombre' => 'required|string|max:100',
            'tipo_doc'        => 'required|in:DNI,CE',
            'num_doc'         => 'required|string',
        ]);

        $paciente = User::firstOrCreate(
            ['num_doc' => $request->num_doc],
            [
                'name'     => $request->paciente_nombre,
                'tipo_doc' => $request->tipo_doc,
                'num_doc'  => $request->num_doc,
                'role'     => 'paciente',
            ]
        );

        Cita::create([
            'paciente_id'     => $paciente->id,
            'especialidad_id' => $request->especialidad_id,
            'medico_id'       => $request->medico_id,
            'fecha_cita'      => $request->fecha_cita,
            'hora_cita'       => $request->hora_cita,
            'estado'          => 'Pendiente',
        ]);

        return redirect()->route('citas.index')
                         ->with('success', 'Cita registrada correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR ESTADO DE CITA
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Cita $cita)
    {
        $request->validate([
            'estado' => 'required|in:Pendiente,Atendida,Cancelada',
        ]);

        $cita->update(['estado' => $request->estado]);

        return redirect()->route('citas.index')
                         ->with('success', 'Estado de cita actualizado.');
    }

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR CITA
    |--------------------------------------------------------------------------
    */
    public function destroy(Cita $cita)
    {
        $cita->delete();
        return redirect()->route('citas.index')
                         ->with('success', 'Cita eliminada correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | API: BUSCAR PACIENTE POR DOCUMENTO
    | Vulnerabilidad #1: SQL Injection (togglable)
    |--------------------------------------------------------------------------
    */
    public function buscarPaciente($num_doc)
    {
        $modoSeguro = $this->getModoSeguro();

        if (!$modoSeguro) {
            // ===============================================================
            // MODO INSEGURO — SQLi REAL en búsqueda de paciente
            // NIST SI-10 DESACTIVADO
            //
            // Payload de ataque via URL:
            //   /api/paciente/12345678' UNION SELECT id,username,password,
            //   role,null,null,null,null,null FROM users --
            //
            // Resultado: devuelve datos de la tabla users completa en JSON,
            // incluyendo credenciales de médicos y admins.
            // ===============================================================
            try {
                $resultado = DB::select(
                    "SELECT id, name, tipo_doc, num_doc FROM users
                     WHERE num_doc = '$num_doc' AND role = 'paciente' LIMIT 1"
                );
            } catch (\Exception $e) {
                $resultado = [];
            }

            $this->registrarLog(
                'SQL Injection (SI-10)',
                'buscar_paciente_inseguro',
                "Búsqueda SQL sin sanitizar. num_doc: $num_doc",
                request(),
                false
            );

            if (!empty($resultado)) {
                return response()->json(['encontrado' => true, 'paciente' => $resultado[0]]);
            }
            return response()->json(['encontrado' => false]);
        }

        // ===================================================================
        // MODO SEGURO — Prepared Statements via Eloquent
        // NIST SI-10: el input es parámetro enlazado, nunca código SQL
        // ===================================================================
        $paciente = User::where('num_doc', $num_doc)
                        ->where('role', 'paciente')
                        ->select('id', 'name', 'tipo_doc', 'num_doc')
                        ->first();

        $this->registrarLog(
            'SQL Injection (SI-10)',
            'buscar_paciente_seguro',
            "Búsqueda con Eloquent prepared statement. num_doc: $num_doc",
            request(),
            false
        );

        if ($paciente) {
            return response()->json(['encontrado' => true, 'paciente' => $paciente]);
        }

        return response()->json(['encontrado' => false]);
    }

    /*
    |--------------------------------------------------------------------------
    | API: DISPONIBILIDAD DEL MÉDICO
    |--------------------------------------------------------------------------
    */
    public function getDisponibilidad($medico_id)
    {
        $programaciones = Programacion::where('user_id', $medico_id)
            ->where('fecha', '>=', now()->toDateString())
            ->with('especialidad')
            ->orderBy('fecha')
            ->get();

        return response()->json($programaciones);
    }

    /*
    |--------------------------------------------------------------------------
    | REPORTES
    |--------------------------------------------------------------------------
    */
    public function reportes(Request $request)
    {
        $query = Cita::with(['paciente.historialClinico', 'especialidad', 'medico']);

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha_cita', [$request->fecha_inicio, $request->fecha_fin]);
        }
        if ($request->filled('especialidad_id')) {
            $query->where('especialidad_id', $request->especialidad_id);
        }
        if ($request->filled('medico_id')) {
            $query->where('medico_id', $request->medico_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $citas          = $query->get();
        $especialidades = Especialidad::all();
        $medicos        = User::where('role', 'medico')->get();

        return view('reportes.index', compact('citas', 'especialidades', 'medicos'));
    }

    public function exportarExcel(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CitasExport($request->all()),
            'reporte_citas_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportarPdf(Request $request)
    {
        $query = Cita::with(['paciente.historialClinico', 'especialidad', 'medico']);

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha_cita', [$request->fecha_inicio, $request->fecha_fin]);
        }
        if ($request->filled('especialidad_id')) {
            $query->where('especialidad_id', $request->especialidad_id);
        }
        if ($request->filled('medico_id')) {
            $query->where('medico_id', $request->medico_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $citas = $query->get();
        $pdf   = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.pdf', compact('citas'));
        return $pdf->download('reporte_citas_' . now()->format('Y-m-d') . '.pdf');
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