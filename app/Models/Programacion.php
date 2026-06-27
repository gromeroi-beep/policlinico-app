<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programacion extends Model
{
    // Nombre correcto de la tabla
    protected $table = 'programaciones';

    // MITIGACIÓN MASS ASSIGNMENT - NIST
    protected $fillable = [
        'especialidad_id',
        'user_id',
        'fecha',
        'hora_inicio',
        'hora_fin'
    ];

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}