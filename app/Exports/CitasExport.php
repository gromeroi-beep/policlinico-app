<?php

namespace App\Exports;

use App\Models\Cita;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CitasExport implements FromView
{
    protected $filtros;

    public function __construct(array $filtros = [])
    {
        $this->filtros = $filtros;
    }

    public function view(): View
    {
        // MITIGACIÓN SQLi: Eloquent con parámetros enlazados
        $query = Cita::with(['paciente', 'especialidad', 'medico']);

        if (!empty($this->filtros['fecha_inicio']) && !empty($this->filtros['fecha_fin'])) {
            $query->whereBetween('fecha_cita', [
                $this->filtros['fecha_inicio'],
                $this->filtros['fecha_fin']
            ]);
        }
        if (!empty($this->filtros['especialidad_id'])) {
            $query->where('especialidad_id', $this->filtros['especialidad_id']);
        }
        if (!empty($this->filtros['medico_id'])) {
            $query->where('medico_id', $this->filtros['medico_id']);
        }
        if (!empty($this->filtros['estado'])) {
            $query->where('estado', $this->filtros['estado']);
        }

        $citas = $query->get();
        return view('reportes.excel', compact('citas'));
    }
}