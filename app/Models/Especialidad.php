<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    // Nombre correcto de la tabla
    protected $table = 'especialidades';

    // MITIGACIÓN MASS ASSIGNMENT - NIST
    protected $fillable = ['descripcion'];

    public function medicos()
    {
        return $this->hasMany(User::class, 'especialidad_id');
    }

    public function programaciones()
    {
        return $this->hasMany(Programacion::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}