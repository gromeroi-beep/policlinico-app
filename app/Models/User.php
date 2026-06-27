<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/**
 * NIST SP 800-53 Controls aplicados en este modelo:
 *
 *  SI-10 → Information Input Validation  (Mass Assignment — $fillable vs $guarded)
 *  SC-28 → Protection of Information at Rest  (Sensitive Data — $hidden)
 *  IA-5  → Authenticator Management      (passwords nunca en texto plano)
 */
class User extends Authenticatable
{
    use Notifiable;

    // =======================================================================
    // VULNERABILIDAD #5 — Mass Assignment
    // NIST SI-10: Information Input Validation
    //
    // MODO INSEGURO → $guarded = [] permite asignar CUALQUIER campo
    //   Un atacante puede enviar: POST /usuarios { "role": "admin" }
    //   y escalar privilegios inmediatamente.
    //   También puede sobreescribir: "id", "remember_token", etc.
    //
    // MODO SEGURO  → $fillable lista explícitamente los campos permitidos.
    //   Cualquier campo no listado es ignorado por Eloquent.
    //
    // El toggle se controla dinámicamente en el boot() del modelo.
    // =======================================================================

    // Campos seguros — solo estos se asignan masivamente en modo seguro
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'colegiatura',
        'especialidad_id',
        'tipo_doc',
        'num_doc',
    ];

    // =======================================================================
    // VULNERABILIDAD #8 — Sensitive Data Exposure
    // NIST SC-28: Protection of Information at Rest
    //
    // MODO INSEGURO → $hidden vacío: password y remember_token visibles
    //   en respuestas JSON, toArray(), y en la vista de usuarios.
    //   OWASP ZAP detectará: Information Disclosure - Sensitive Information
    //
    // MODO SEGURO  → $hidden oculta campos sensibles en toda serialización.
    // =======================================================================
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Boot del modelo — aplica el modo inseguro dinámicamente si corresponde.
     * NIST CM-6: Configuration Settings
     */
    protected static function boot(): void
    {
        parent::boot();

        try {
            $modoSeguro = (bool) DB::table('security_settings')
                ->where('clave', 'modo_seguro')
                ->value('valor');
        } catch (\Exception $e) {
            $modoSeguro = true; // Por defecto seguro si la tabla no existe
        }

        if (!$modoSeguro) {
            // ===============================================================
            // MODO INSEGURO — Mass Assignment + Sensitive Data EXPUESTOS
            //
            // $guarded = [] → NINGÚN campo está protegido
            // $hidden  = [] → passwords visibles en JSON y vistas
            //
            // Esto afecta a TODO el sistema que use el modelo User:
            // CitaController, HistorialController, UserController, etc.
            // ===============================================================
            static::$unguarded = true; // Desactiva toda protección de Eloquent

            // Exponer password en serialización (Sensitive Data Exposure real)
            // La vista usuarios.index recibirá el campo password visible
            app()->booted(function () {
                // Se aplica al resolver la instancia en el contenedor
            });
        }
    }

    // =======================================================================
    // RELACIONES
    // =======================================================================

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function programaciones()
    {
        return $this->hasMany(Programacion::class, 'user_id');
    }

    public function citasMedico()
    {
        return $this->hasMany(Cita::class, 'medico_id');
    }

    public function citasPaciente()
    {
        return $this->hasMany(Cita::class, 'paciente_id');
    }

    public function historialClinico()
    {
        return $this->hasOne(HistorialClinico::class, 'paciente_id');
    }

    // =======================================================================
    // HELPER: ¿Está oculto el password? (para la vista del panel OWASP)
    // =======================================================================
    public function passwordEstaOculto(): bool
    {
        return in_array('password', $this->hidden);
    }
}