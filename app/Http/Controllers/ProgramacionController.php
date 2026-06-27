<?php

namespace App\Http\Controllers;

use App\Models\Programacion;
use App\Models\Especialidad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProgramacionController extends Controller
{
    public function index()
    {
        // 🔥 FORZAR RECARGA DE DATOS
        $programaciones = Programacion::with(['especialidad', 'medico'])
            ->orderBy('id', 'desc')
            ->get();
        
        $especialidades = Especialidad::all();
        $timestamp = time();
        
        // 🔥 HEADERS PARA EVITAR CACHÉ
        return response()
            ->view('programaciones.index', compact('programaciones', 'especialidades', 'timestamp'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function store(Request $request)
    {
        $request->validate([
            'especialidad_id' => 'required|exists:especialidades,id',
            'user_id'         => 'required|exists:users,id',
            'fecha'           => 'required|date',
            'hora_inicio'     => 'required',
            'hora_fin'        => 'required|after:hora_inicio',
        ]);

        $cruce = Programacion::where('user_id', $request->user_id)
            ->where('fecha', $request->fecha)
            ->where(function($query) use ($request) {
                $query->whereBetween('hora_inicio', [$request->hora_inicio, $request->hora_fin])
                      ->orWhereBetween('hora_fin', [$request->hora_inicio, $request->hora_fin]);
            })->exists();

        if ($cruce) {
            return back()->withErrors([
                'hora_inicio' => 'El médico ya tiene una programación en ese horario.'
            ])->withInput();
        }

        Programacion::create($request->only([
            'especialidad_id', 'user_id', 'fecha', 'hora_inicio', 'hora_fin'
        ]));

        return redirect()->route('programaciones.index')
                         ->with('success', 'Programación registrada correctamente.');
    }

    public function destroy($id)  // <-- 🔥 CAMBIO AQUÍ: recibe $id directamente
    {
        try {
            Log::info('🔍 Intentando eliminar programación ID: ' . $id);
            
            // 🔥 ELIMINAR CON DB DIRECTAMENTE
            $deleted = DB::table('programaciones')->where('id', $id)->delete();
            
            Log::info('✅ Resultado DB::delete(): ' . ($deleted ? 'EXITOSO (eliminó ' . $deleted . ' fila)' : 'FALLIDO'));
            
            if ($deleted) {
                return redirect()->route('programaciones.index')
                    ->with('success', 'Programación eliminada correctamente.');
            } else {
                return redirect()->route('programaciones.index')
                    ->with('error', 'No se encontró la programación para eliminar.');
            }
            
        } catch (\Exception $e) {
            Log::error('❌ Error al eliminar: ' . $e->getMessage());
            return redirect()->route('programaciones.index')
                ->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function getMedicos($especialidad_id)
    {
        $medicos = User::where('especialidad_id', $especialidad_id)
                       ->where('role', 'medico')
                       ->select('id', 'name', 'colegiatura')
                       ->get();

        return response()->json($medicos);
    }
}