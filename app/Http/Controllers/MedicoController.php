<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\HistorialClinico;
use App\Models\Programacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MedicoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD DEL MÉDICO
    |--------------------------------------------------------------------------
    */
    public function dashboard()
    {
        $medico = Auth::user();

        $totalCitas      = Cita::where('medico_id', $medico->id)->count();
        $citasPendientes = Cita::where('medico_id', $medico->id)
                               ->where('estado', 'Pendiente')->count();
        $citasAtendidas  = Cita::where('medico_id', $medico->id)
                               ->where('estado', 'Atendida')->count();
        $citasHoy        = Cita::where('medico_id', $medico->id)
                               ->where('fecha_cita', now()->toDateString())->count();

        $proximasCitas = Cita::where('medico_id', $medico->id)
                             ->where('fecha_cita', '>=', now()->toDateString())
                             ->where('estado', 'Pendiente')
                             ->with(['paciente', 'especialidad'])
                             ->orderBy('fecha_cita')
                             ->orderBy('hora_cita')
                             ->take(5)
                             ->get();

        return view('medico.dashboard', compact(
            'medico',
            'totalCitas',
            'citasPendientes',
            'citasAtendidas',
            'citasHoy',
            'proximasCitas'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | CITAS DEL MÉDICO
    |--------------------------------------------------------------------------
    */
    public function citas()
    {
        $medico = Auth::user();

        $citas = Cita::where('medico_id', $medico->id)
                     ->with(['paciente', 'especialidad'])
                     ->orderBy('fecha_cita', 'desc')
                     ->get();

        return view('medico.citas', compact('citas', 'medico'));
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR ESTADO DE CITA
    |--------------------------------------------------------------------------
    */
    public function updateEstadoCita(Request $request, Cita $cita)
    {
        $medico = Auth::user();

        if ($cita->medico_id !== $medico->id) {
            abort(403, 'No autorizado — Esta cita no le pertenece.');
        }

        $request->validate([
            'estado' => 'required|in:Pendiente,Atendida,Cancelada',
        ]);

        $cita->update(['estado' => $request->estado]);

        return redirect()->route('medico.citas')
                         ->with('success', 'Estado de cita actualizado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | HISTORIALES CLÍNICOS DEL MÉDICO
    |--------------------------------------------------------------------------
    */
    public function historiales()
    {
        $medico = Auth::user();

        $pacientesIds = Cita::where('medico_id', $medico->id)
                            ->pluck('paciente_id')
                            ->unique();

        $pacientes = User::whereIn('id', $pacientesIds)
                         ->where('role', 'paciente')
                         ->get();

        $especialidades = Especialidad::all();

        return view('medico.historiales', compact('pacientes', 'especialidades', 'medico'));
    }

    /*
    |--------------------------------------------------------------------------
    | BUSCAR HISTORIAL POR DOCUMENTO
    |--------------------------------------------------------------------------
    */
    public function buscarHistorial(Request $request)
    {
        $medico = Auth::user();

        $request->validate([
            'num_doc' => 'required|string',
        ]);

        $paciente = User::where('num_doc', $request->num_doc)
                        ->where('role', 'paciente')
                        ->first();

        if (!$paciente) {
            return back()->withErrors([
                'num_doc' => 'No se encontró ningún paciente con ese documento.'
            ])->withInput();
        }

        $esSupaciente = Cita::where('medico_id', $medico->id)
                            ->where('paciente_id', $paciente->id)
                            ->exists();

        if (!$esSupaciente) {
            abort(403, 'No autorizado — Este paciente no está en su lista de atendidos.');
        }

        $historial = HistorialClinico::where('paciente_id', $paciente->id)->first();

        return view('medico.historial_show', compact('paciente', 'historial', 'medico'));
    }

    /*
    |--------------------------------------------------------------------------
    | GUARDAR HISTORIAL CLÍNICO
    |--------------------------------------------------------------------------
    */
    public function storeHistorial(Request $request)
    {
        $medico = Auth::user();

        $request->validate([
            'paciente_id'           => 'required|exists:users,id',
            'grupo_sanguineo'       => 'nullable|string|max:5',
            'alergias'              => 'nullable|string',
            'diagnostico_principal' => 'nullable|string',
            'antecedentes_medicos'  => 'nullable|string',
            'estado_paciente'       => 'required|in:Estable,En Tratamiento,Crítico',
            'observaciones'         => 'nullable|string',
        ]);

        $esSupaciente = Cita::where('medico_id', $medico->id)
                            ->where('paciente_id', $request->paciente_id)
                            ->exists();

        if (!$esSupaciente) {
            abort(403, 'No autorizado — Este paciente no está en su lista de atendidos.');
        }

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

        return redirect()->route('medico.historiales')
                         ->with('success', 'Historial clínico guardado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | VER HISTORIAL DE UN PACIENTE
    |--------------------------------------------------------------------------
    */
    public function showHistorial(User $paciente)
    {
        $medico = Auth::user();

        $esSupaciente = Cita::where('medico_id', $medico->id)
                            ->where('paciente_id', $paciente->id)
                            ->exists();

        if (!$esSupaciente) {
            abort(403, 'No autorizado — Este paciente no está en su lista de atendidos.');
        }

        $historial = HistorialClinico::where('paciente_id', $paciente->id)->first();

        return view('medico.historial_show', compact('paciente', 'historial', 'medico'));
    }

    /*
    |--------------------------------------------------------------------------
    | EDITAR HISTORIAL (MÉDICO)
    |--------------------------------------------------------------------------
    */
    public function editHistorial($paciente_id)
    {
        $medico = Auth::user();

        $paciente = User::findOrFail($paciente_id);
        
        $esSupaciente = Cita::where('medico_id', $medico->id)
                            ->where('paciente_id', $paciente->id)
                            ->exists();

        if (!$esSupaciente) {
            abort(403, 'No autorizado — Este paciente no está en su lista de atendidos.');
        }

        $historial = HistorialClinico::where('paciente_id', $paciente->id)->first();
        
        if (!$historial) {
            return redirect()->route('medico.historiales')
                ->with('error', 'Este paciente no tiene historial clínico.');
        }

        return view('medico.historial_edit', compact('paciente', 'historial', 'medico'));
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR HISTORIAL (MÉDICO)
    |--------------------------------------------------------------------------
    */
    public function updateHistorial(Request $request, $paciente_id)
    {
        $medico = Auth::user();

        $request->validate([
            'grupo_sanguineo' => 'nullable|string|max:5',
            'alergias' => 'nullable|string',
            'diagnostico_principal' => 'nullable|string',
            'antecedentes_medicos' => 'nullable|string',
            'estado_paciente' => 'required|in:Estable,En Tratamiento,Crítico',
            'observaciones' => 'nullable|string',
        ]);

        $esSupaciente = Cita::where('medico_id', $medico->id)
                            ->where('paciente_id', $paciente_id)
                            ->exists();

        if (!$esSupaciente) {
            abort(403, 'No autorizado — Este paciente no está en su lista de atendidos.');
        }

        $historial = HistorialClinico::where('paciente_id', $paciente_id)->first();
        
        if (!$historial) {
            return redirect()->route('medico.historiales')
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

        return redirect()->route('medico.historiales.show', $paciente_id)
                         ->with('success', 'Historial clínico actualizado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR HISTORIAL (MÉDICO)
    |--------------------------------------------------------------------------
    */
    public function destroyHistorial($paciente_id)
    {
        $medico = Auth::user();

        $esSupaciente = Cita::where('medico_id', $medico->id)
                            ->where('paciente_id', $paciente_id)
                            ->exists();

        if (!$esSupaciente) {
            abort(403, 'No autorizado — Este paciente no está en su lista de atendidos.');
        }

        $historial = HistorialClinico::where('paciente_id', $paciente_id)->first();
        
        if (!$historial) {
            return redirect()->route('medico.historiales')
                ->with('error', 'No se encontró el historial clínico.');
        }

        $historial->delete();

        return redirect()->route('medico.historiales')
                         ->with('success', 'Historial clínico eliminado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | REPORTES DEL MÉDICO
    |--------------------------------------------------------------------------
    */
    public function reportes(Request $request)
    {
        $medico = Auth::user();

        $query = Cita::where('medico_id', $medico->id)
                     ->with(['paciente.historialClinico', 'especialidad']);

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha_cita', [
                $request->fecha_inicio,
                $request->fecha_fin
            ]);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $citas = $query->orderBy('fecha_cita', 'desc')->get();

        return view('medico.reportes', compact('citas', 'medico'));
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORTAR EXCEL DEL MÉDICO
    |--------------------------------------------------------------------------
    */
    public function exportarExcel(Request $request)
    {
        $medico = Auth::user();

        $filtros = array_merge($request->all(), ['medico_id' => $medico->id]);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CitasExport($filtros),
            'mis_citas_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORTAR PDF DEL MÉDICO
    |--------------------------------------------------------------------------
    */
    public function exportarPdf(Request $request)
    {
        $medico = Auth::user();

        $query = Cita::where('medico_id', $medico->id)
                     ->with(['paciente.historialClinico', 'especialidad']);

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha_cita', [
                $request->fecha_inicio,
                $request->fecha_fin
            ]);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $citas = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.pdf', compact('citas'));
        return $pdf->download('mis_citas_' . now()->format('Y-m-d') . '.pdf');
    }
}