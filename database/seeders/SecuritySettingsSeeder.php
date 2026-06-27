<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SecuritySettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar datos existentes
        DB::table('security_settings')->truncate();

        // Insertar configuración de seguridad
        DB::table('security_settings')->insert([
            // Toggle global — todas las vulnerabilidades activadas por defecto
            [
                'clave'       => 'modo_seguro',
                'valor'       => 0, // 0 = inseguro (para demostración)
                'descripcion' => 'Toggle global: 1=protegido (NIST activo), 0=vulnerable (OWASP expuesto)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            // Toggle individual por vulnerabilidad
            [
                'clave'       => 'vuln_sqli',
                'valor'       => 0,
                'descripcion' => 'OWASP A03 - SQL Injection — Control NIST SI-10',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_xss',
                'valor'       => 0,
                'descripcion' => 'OWASP A03 - XSS — Control NIST SI-10 + SC-18',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_command',
                'valor'       => 0,
                'descripcion' => 'OWASP A03 - OS Command Injection — Control NIST SI-10',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_csrf',
                'valor'       => 0,
                'descripcion' => 'OWASP A01 - CSRF — Control NIST SC-8 + SI-10',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_mass_assignment',
                'valor'       => 0,
                'descripcion' => 'OWASP A08 - Mass Assignment — Control NIST SI-10',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_broken_access',
                'valor'       => 0,
                'descripcion' => 'OWASP A01 - Broken Access Control — Control NIST AC-3',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_misconfig',
                'valor'       => 0,
                'descripcion' => 'OWASP A05 - Security Misconfiguration — Control NIST CM-6 + CM-7',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_sensitive_data',
                'valor'       => 0,
                'descripcion' => 'OWASP A02 - Sensitive Data Exposure — Control NIST SC-28 + IA-5',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_brute_force',
                'valor'       => 0,
                'descripcion' => 'OWASP A07 - Brute Force / Rate Limiting — Control NIST AC-7',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'vuln_traversal',
                'valor'       => 0,
                'descripcion' => 'OWASP A01 - Directory Traversal — Control NIST AC-3 + SI-10',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}