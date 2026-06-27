<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    // MITIGACIÓN MASS ASSIGNMENT - NIST
    protected $fillable = [
        'paciente_id',
        'especialidad_id',
        'medico_id',
        'fecha_cita',
        'hora_cita',
        'estado'
    ];

    // Relaciones
    public function paciente()
    {
        return $this->belongsTo(User::class, 'paciente_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id');
    }
}