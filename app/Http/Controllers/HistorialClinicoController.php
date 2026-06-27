<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialClinico extends Model
{
    // Nombre explícito de tabla para evitar pluralización incorrecta
    protected $table = 'historiales_clinicos';

    // =========================================================================
    // MITIGACIÓN MASS ASSIGNMENT - NIST CSF (Función PROTEGER)
    // =========================================================================
    // ATAQUE SIMULADO: Sin $fillable, un atacante podría enviar via HTTP:
    //   POST /historiales { "paciente_id": 1, "estado_paciente": "Crítico",
    //                       "codigo_historial": "HC-00001" }
    // alterando masivamente el estado clínico de cualquier paciente
    // o inyectando códigos de historial duplicados para causar
    // inconsistencias en la base de datos médica.
    //
    // MITIGACIÓN: Solo los campos listados en $fillable pueden ser
    // asignados masivamente. Cualquier campo fuera de esta lista
    // es ignorado automáticamente por Eloquent.
    // =========================================================================
    protected $fillable = [
        'paciente_id',
        'codigo_historial',
        'grupo_sanguineo',
        'alergias',
        'diagnostico_principal',
        'antecedentes_medicos',
        'estado_paciente',
        'observaciones',
    ];

    // Relación con el paciente
    public function paciente()
    {
        return $this->belongsTo(User::class, 'paciente_id');
    }
}