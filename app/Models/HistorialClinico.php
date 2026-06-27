<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * NOTA: Este archivo debe guardarse en app/Models/HistorialClinico.php
 * El archivo que recibiste como "HistorialClinicoController.php" es en
 * realidad el modelo, no un controlador. Está en la carpeta correcta.
 *
 * NIST SP 800-53 Controls aplicados en este modelo:
 *
 *  SI-10 → Information Input Validation  (Mass Assignment — $fillable)
 *  SC-28 → Protection of Information at Rest  (datos clínicos sensibles)
 */
class HistorialClinico extends Model
{
    // Nombre explícito de tabla
    protected $table = 'historiales_clinicos';

    // =======================================================================
    // VULNERABILIDAD #5 — Mass Assignment en datos clínicos
    // NIST SI-10: Information Input Validation
    //
    // MODO INSEGURO → $guarded = [] permite sobrescribir cualquier campo:
    //   Un atacante podría enviar:
    //   POST /historiales { "paciente_id": 5, "estado_paciente": "Crítico",
    //                       "diagnostico_principal": "VIH positivo" }
    //   Alterando el historial médico de cualquier paciente.
    //   O inyectando: "codigo_historial": "HC-00001" para crear duplicados.
    //
    // MODO SEGURO  → $fillable lista solo los campos permitidos.
    // =======================================================================
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

    /**
     * Boot — aplica modo inseguro dinámicamente si el toggle está OFF.
     */
    protected static function boot(): void
    {
        parent::boot();

        try {
            $modoSeguro = (bool) DB::table('security_settings')
                ->where('clave', 'modo_seguro')
                ->value('valor');
        } catch (\Exception $e) {
            $modoSeguro = true;
        }

        if (!$modoSeguro) {
            // ===============================================================
            // MODO INSEGURO — Mass Assignment sin restricciones
            // Cualquier campo del request puede sobrescribir datos clínicos
            // ===============================================================
            static::$unguarded = true;
        }
    }

    // =======================================================================
    // RELACIONES
    // =======================================================================

    public function paciente()
    {
        return $this->belongsTo(User::class, 'paciente_id');
    }
}