<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migración: Infraestructura de seguridad
 *
 * Crea las tablas necesarias para el sistema de monitoreo OWASP/NIST:
 *   - security_settings : configuración global (modo seguro ON/OFF por vuln.)
 *   - security_logs     : registro de eventos de seguridad detectados
 */
return new class extends Migration
{
    public function up(): void
    {
        // -------------------------------------------------------------------
        // TABLA: security_settings
        // Controla el modo seguro/inseguro de cada vulnerabilidad
        // El panel OWASP lee y escribe aquí
        // -------------------------------------------------------------------
        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 100)->unique();   // ej: 'modo_seguro', 'debug_activo'
            $table->tinyInteger('valor')->default(1); // 1 = seguro, 0 = inseguro
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------------------
        // TABLA: security_logs
        // Registra cada evento de ataque detectado o protegido
        // El panel OWASP muestra estos logs en tiempo real
        // -------------------------------------------------------------------
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('vulnerabilidad', 100);   // ej: 'SQL Injection (SI-10)'
            $table->string('tipo', 100);              // ej: 'login_inseguro', 'ip_bloqueada'
            $table->text('descripcion');              // detalle del evento
            $table->string('ip', 45)->nullable();     // IPv4 o IPv6
            $table->text('user_agent')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable(); // null si no autenticado
            $table->boolean('bloqueado')->default(false); // true = ataque bloqueado
            $table->timestamps();

            $table->index('vulnerabilidad');
            $table->index('bloqueado');
            $table->index('created_at');
        });

        // -------------------------------------------------------------------
        // DATOS INICIALES: las 10 vulnerabilidades — todas en modo SEGURO
        // El profesor verá el sistema protegido por defecto al ingresar
        // -------------------------------------------------------------------
        DB::table('security_settings')->insert([
            // Toggle global — afecta a todos los controladores
            [
                'clave'       => 'modo_seguro',
                'valor'       => 1,
                'descripcion' => 'Toggle global: 1=protegido (NIST activo), 0=vulnerable (OWASP expuesto)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            // Toggle individual por vulnerabilidad (para granularidad en demo)
            [
                'clave'       => 'vuln_sqli',
                'valor'       => 1,
                'descripcion' => 'OWASP A03 - SQL Injection — Control NIST SI-10',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_xss',
                'valor'       => 1,
                'descripcion' => 'OWASP A03 - XSS — Control NIST SI-10 + SC-18',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_command',
                'valor'       => 1,
                'descripcion' => 'OWASP A03 - OS Command Injection — Control NIST SI-10',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_csrf',
                'valor'       => 1,
                'descripcion' => 'OWASP A01 - CSRF — Control NIST SC-8 + SI-10',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_mass_assignment',
                'valor'       => 1,
                'descripcion' => 'OWASP A08 - Mass Assignment — Control NIST SI-10',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_broken_access',
                'valor'       => 1,
                'descripcion' => 'OWASP A01 - Broken Access Control — Control NIST AC-3',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_misconfig',
                'valor'       => 1,
                'descripcion' => 'OWASP A05 - Security Misconfiguration — Control NIST CM-6 + CM-7',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_sensitive_data',
                'valor'       => 1,
                'descripcion' => 'OWASP A02 - Sensitive Data Exposure — Control NIST SC-28 + IA-5',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_brute_force',
                'valor'       => 1,
                'descripcion' => 'OWASP A07 - Brute Force / Rate Limiting — Control NIST AC-7',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_traversal',
                'valor'       => 1,
                'descripcion' => 'OWASP A01 - Directory Traversal — Control NIST AC-3 + SI-10',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('security_logs');
        Schema::dropIfExists('security_settings');
    }
};