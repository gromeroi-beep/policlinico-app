<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EspecialidadController extends Controller
{
    public function index()
    {
        $especialidades = Especialidad::all();
        $total = $especialidades->count();
        return view('especialidades.index', compact('especialidades', 'total'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:100|unique:especialidades,descripcion',
        ]);

        Especialidad::create($request->only('descripcion'));

        return redirect()->route('especialidades.index')
                         ->with('success', 'Especialidad registrada correctamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'descripcion' => 'required|string|max:100|unique:especialidades,descripcion,' . $id,
        ]);

        $especialidad = Especialidad::findOrFail($id);
        $especialidad->update($request->only('descripcion'));

        return redirect()->route('especialidades.index')
                         ->with('success', 'Especialidad actualizada correctamente.');
    }

    public function destroy($id)
    {
        try {
            Log::info('🔍 Intentando eliminar especialidad ID: ' . $id);
            
            // 🔥 Verificar si existe
            $especialidad = Especialidad::find($id);
            
            if (!$especialidad) {
                Log::warning('⚠️ Especialidad no encontrada: ' . $id);
                return redirect()->route('especialidades.index')
                    ->with('error', '❌ Especialidad no encontrada.');
            }
            
            // 🔥 VERIFICAR REGISTROS RELACIONADOS
            $medicos = DB::table('users')->where('especialidad_id', $id)->count();
            $programaciones = DB::table('programaciones')->where('especialidad_id', $id)->count();
            $citas = DB::table('citas')->where('especialidad_id', $id)->count();
            
            Log::info('📊 Relaciones - Médicos: ' . $medicos . ', Programaciones: ' . $programaciones . ', Citas: ' . $citas);
            
            // 🔥 SI TIENE RELACIONES
            if ($medicos > 0 || $programaciones > 0 || $citas > 0) {
                $items = [];
                if ($medicos > 0) $items[] = $medicos . ' médico(s)';
                if ($programaciones > 0) $items[] = $programaciones . ' programación(es)';
                if ($citas > 0) $items[] = $citas . ' cita(s)';
                $mensaje = '⚠️ No se puede eliminar porque tiene: ' . implode(', ', $items) . '. Elimine primero los registros relacionados.';
                
                return redirect()->route('especialidades.index')
                    ->with('error', $mensaje);
            }
            
            // 🔥 ELIMINAR CON EL MODELO
            $deleted = $especialidad->delete();
            
            Log::info('✅ Resultado delete(): ' . ($deleted ? 'EXITOSO' : 'FALLIDO'));
            
            if ($deleted) {
                return redirect()->route('especialidades.index')
                    ->with('success', '✅ Especialidad eliminada correctamente.');
            } else {
                // 🔥 SI FALLA, USAR DB DIRECTAMENTE
                Log::info('⚠️ delete() falló, usando DB::delete()');
                $deletedDb = DB::table('especialidades')->where('id', $id)->delete();
                Log::info('✅ DB::delete() resultado: ' . ($deletedDb ? 'EXITOSO' : 'FALLIDO'));
                
                if ($deletedDb) {
                    return redirect()->route('especialidades.index')
                        ->with('success', '✅ Especialidad eliminada correctamente.');
                } else {
                    return redirect()->route('especialidades.index')
                        ->with('error', '❌ No se pudo eliminar la especialidad.');
                }
            }
            
        } catch (\Exception $e) {
            Log::error('❌ Error: ' . $e->getMessage());
            Log::error('❌ Trace: ' . $e->getTraceAsString());
            return redirect()->route('especialidades.index')
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    }
}