@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>⚙ Configuración del Sistema — Admin</h1>
    <p>Ruta: <code style="background:#e8eaf6;padding:2px 8px;border-radius:4px;font-size:13px;">/admin/config</code></p>
</div>

{{-- ══════════════════════════════════════════════════════════════
     VULNERABILIDAD #6 — Broken Access Control
     Esta vista demuestra si un médico pudo acceder aquí sin ser admin
     ══════════════════════════════════════════════════════════════ --}}
@if(!$modoSeguro)
<div style="
    background:#ffebee;border:1.5px solid #ef9a9a;border-left:5px solid #c62828;
    border-radius:8px;padding:16px 20px;margin-bottom:24px;
">
    <div style="font-weight:700;color:#b71c1c;font-size:15px;margin-bottom:8px;">
        🔴 BROKEN ACCESS CONTROL ACTIVO — Vulnerabilidad #6 (OWASP A01:2021)
    </div>
    <div style="font-size:13px;color:#c62828;line-height:1.7;">
        Un usuario con rol <strong>médico</strong> puede acceder a esta ruta escribiendo
        <code style="background:#ffcdd2;padding:2px 6px;border-radius:3px;">/admin/config</code>
        directamente en el navegador, sin ninguna restricción.<br>
        <strong>Control NIST AC-3 (Access Enforcement) está DESACTIVADO.</strong><br>
        OWASP ZAP detectará: <em>Broken Access Control — Unauthorized Access to Admin Resources</em>
    </div>
    <div style="margin-top:12px;font-size:12px;color:#e53935;">
        Usuario actual: <strong>{{ auth()->user()->name }}</strong>
        — Rol: <strong>{{ auth()->user()->role }}</strong>
        @if(auth()->user()->role !== 'admin')
            <span style="background:#c62828;color:#fff;padding:2px 8px;border-radius:4px;margin-left:8px;font-weight:700;">
                ⚠ NO ES ADMIN — Acceso no autorizado permitido
            </span>
        @endif
    </div>
</div>
@else
<div style="
    background:#e8f5e9;border:1.5px solid #a5d6a7;border-left:5px solid #2e7d32;
    border-radius:8px;padding:16px 20px;margin-bottom:24px;
">
    <div style="font-weight:700;color:#1b5e20;font-size:15px;margin-bottom:6px;">
        🟢 MODO SEGURO — Control NIST AC-3 Activo
    </div>
    <div style="font-size:13px;color:#2e7d32;">
        Solo el <strong>administrador</strong> puede acceder a esta ruta.
        Si un médico intenta acceder, el middleware <code>CheckRole</code> lo rechaza con HTTP 403
        y el intento queda registrado en <code>security_logs</code>.
    </div>
</div>
@endif

{{-- Contenido de configuración (ficticio para la demo) --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card">
        <div style="font-weight:700;font-size:14px;color:#1a237e;margin-bottom:16px;">
            📊 Información del Sistema
        </div>
        <table style="font-size:13px;">
            <tr style="border-bottom:1px solid #f0f0f0;">
                <td style="padding:8px 0;color:#607d8b;font-weight:600;width:160px;">Framework</td>
                <td style="padding:8px 0;">Laravel {{ app()->version() }}</td>
            </tr>
            <tr style="border-bottom:1px solid #f0f0f0;">
                <td style="padding:8px 0;color:#607d8b;font-weight:600;">PHP Version</td>
                <td style="padding:8px 0;">{{ phpversion() }}</td>
            </tr>
            <tr style="border-bottom:1px solid #f0f0f0;">
                <td style="padding:8px 0;color:#607d8b;font-weight:600;">Entorno</td>
                <td style="padding:8px 0;">{{ app()->environment() }}</td>
            </tr>
            <tr style="border-bottom:1px solid #f0f0f0;">
                <td style="padding:8px 0;color:#607d8b;font-weight:600;">APP_DEBUG</td>
                <td style="padding:8px 0;">
                    <span style="
                        padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;
                        background: {{ config('app.debug') ? '#ffebee' : '#e8f5e9' }};
                        color: {{ config('app.debug') ? '#c62828' : '#2e7d32' }};
                    ">
                        {{ config('app.debug') ? 'TRUE (Inseguro)' : 'FALSE (Seguro)' }}
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding:8px 0;color:#607d8b;font-weight:600;">Base de Datos</td>
                <td style="padding:8px 0;">{{ config('database.default') }}</td>
            </tr>
        </table>
    </div>

    <div class="card">
        <div style="font-weight:700;font-size:14px;color:#1a237e;margin-bottom:16px;">
            🛡 Estado de Controles NIST
        </div>
        @php
            try {
                $settings = \Illuminate\Support\Facades\DB::table('security_settings')
                    ->whereIn('clave', ['vuln_sqli','vuln_csrf','vuln_broken_access','vuln_misconfig'])
                    ->pluck('valor', 'clave');
            } catch (\Exception $e) {
                $settings = collect([]);
            }
        @endphp
        <table style="font-size:12px;">
            @foreach([
                'vuln_sqli'          => 'SI-10 — SQL Injection',
                'vuln_csrf'          => 'SC-8  — CSRF',
                'vuln_broken_access' => 'AC-3  — Access Control',
                'vuln_misconfig'     => 'CM-6  — Misconfig',
            ] as $clave => $nombre)
            <tr style="border-bottom:1px solid #f0f0f0;">
                <td style="padding:7px 0;color:#455a64;font-weight:600;">{{ $nombre }}</td>
                <td style="padding:7px 0;text-align:right;">
                    @php $val = $settings[$clave] ?? 1; @endphp
                    <span style="
                        padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700;
                        background: {{ $val ? '#e8f5e9' : '#ffebee' }};
                        color: {{ $val ? '#1b5e20' : '#c62828' }};
                    ">
                        {{ $val ? '✅ Protegido' : '🔴 Vulnerable' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </table>

        <div style="margin-top:16px;padding-top:14px;border-top:1px solid #f0f0f0;">
            <a href="{{ route('seguridad.index') }}" class="btn btn-primary btn-sm">
                🛡 Ver Panel OWASP completo
            </a>
        </div>
    </div>
</div>
@endsection