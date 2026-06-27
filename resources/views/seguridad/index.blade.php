@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>🛡 Panel de Seguridad OWASP / NIST SP 800-53</h1>
    <p>Estado en tiempo real de las 10 vulnerabilidades del sistema — Policlínico Flores</p>
</div>

{{-- TOGGLE GLOBAL --}}
<div class="card" style="border: 2px solid {{ $modoSeguro ? '#2e7d32' : '#c62828' }}; margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <div style="
                    width: 16px; height: 16px; border-radius: 50%;
                    background: {{ $modoSeguro ? '#2e7d32' : '#c62828' }};
                    box-shadow: 0 0 0 4px {{ $modoSeguro ? 'rgba(46,125,50,.2)' : 'rgba(198,40,40,.2)' }};
                    animation: {{ $modoSeguro ? 'none' : 'pulse-red 1.5s infinite' }};
                "></div>
                <h2 style="font-size: 20px; color: {{ $modoSeguro ? '#1b5e20' : '#b71c1c' }}; font-weight: 800;">
                    @if($modoSeguro)
                        MODO SEGURO — Controles NIST SP 800-53 ACTIVOS
                    @else
                        ⚠ MODO INSEGURO — Sistema EXPUESTO a las 10 vulnerabilidades
                    @endif
                </h2>
            </div>
            <p style="color: #607d8b; font-size: 13px; max-width: 600px;">
                @if($modoSeguro)
                    Todos los controles NIST están activos. Un escaneo con OWASP ZAP
                    no detectará vulnerabilidades en el sistema.
                @else
                    El sistema está operando sin protecciones. Un escaneo con OWASP ZAP
                    detectará las 10 vulnerabilidades en rojo. Los ataques ocurren en
                    el sistema real — no es una simulación.
                @endif
            </p>
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             FORMULARIO CORREGIDO — URL ABSOLUTA
             ═══════════════════════════════════════════════════════════ --}}
        <form action="{{ url('/seguridad/toggle') }}" method="POST">
            @csrf
            <button type="submit" class="btn {{ $modoSeguro ? 'btn-danger' : 'btn-success' }}"
                    style="padding: 14px 28px; font-size: 15px; border-radius: 10px;"
                    onclick="return confirm('¿Confirmar cambio de modo de seguridad?')">
                @if($modoSeguro)
                    🔴 Desactivar Protecciones (Exponer Vulnerabilidades)
                @else
                    🟢 Activar Protecciones NIST (Proteger Sistema)
                @endif
            </button>
        </form>
    </div>
</div>

{{-- CONTADORES DE EVENTOS --}}
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 28px;">
    <div class="card" style="text-align: center; border-top: 4px solid #0d47a1; padding: 20px;">
        <div style="font-size: 36px; font-weight: 800; color: #0d47a1;">{{ $totalAtaques }}</div>
        <div style="color: #607d8b; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-top: 4px;">
            Eventos Totales
        </div>
    </div>
    <div class="card" style="text-align: center; border-top: 4px solid #c62828; padding: 20px;">
        <div style="font-size: 36px; font-weight: 800; color: #c62828;">{{ $detectados }}</div>
        <div style="color: #607d8b; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-top: 4px;">
            Detectados (No bloqueados)
        </div>
    </div>
    <div class="card" style="text-align: center; border-top: 4px solid #2e7d32; padding: 20px;">
        <div style="font-size: 36px; font-weight: 800; color: #2e7d32;">{{ $bloqueados }}</div>
        <div style="color: #607d8b; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-top: 4px;">
            Bloqueados por NIST
        </div>
    </div>
</div>

{{-- TABLA DE LAS 10 VULNERABILIDADES --}}
<div class="card">
    <h3 style="margin-bottom: 20px; font-size: 16px; color: #1a237e; font-weight: 700;">
        📋 Estado de las 10 Vulnerabilidades OWASP Top 10
    </h3>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Vulnerabilidad</th>
                    <th>OWASP</th>
                    <th>Control NIST</th>
                    <th>Módulo Afectado</th>
                    <th>Riesgo</th>
                    <th style="width: 140px; text-align: center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vulnerabilidades as $vuln)
                <tr>
                    <td style="font-weight: 700; color: #607d8b;">{{ $vuln['id'] }}</td>
                    <td>
                        <div style="font-weight: 600; color: #263238;">{{ $vuln['nombre'] }}</div>
                        <div style="font-size: 11px; color: #90a4ae; margin-top: 2px;">
                            {{ $vuln['descripcion'] }}
                        </div>
                    </td>
                    <td>
                        <span style="
                            background: #e3f2fd; color: #0d47a1;
                            padding: 3px 8px; border-radius: 4px;
                            font-size: 11px; font-weight: 700;
                        ">{{ $vuln['owasp'] }}</span>
                    </td>
                    <td>
                        <span style="
                            background: #f3e5f5; color: #6a1b9a;
                            padding: 3px 8px; border-radius: 4px;
                            font-size: 11px; font-weight: 700;
                        ">{{ $vuln['nist'] }}</span>
                    </td>
                    <td style="font-size: 12px; color: #607d8b;">{{ $vuln['modulo'] }}</td>
                    <td>
                        @php
                            $colores = [
                                'CRÍTICO' => ['bg' => '#ffebee', 'txt' => '#c62828'],
                                'ALTO'    => ['bg' => '#fff3e0', 'txt' => '#e65100'],
                                'MEDIO'   => ['bg' => '#fff8e1', 'txt' => '#f57f17'],
                            ];
                            $c = $colores[$vuln['riesgo']] ?? ['bg' => '#f5f5f5', 'txt' => '#616161'];
                        @endphp
                        <span style="
                            background: {{ $c['bg'] }}; color: {{ $c['txt'] }};
                            padding: 3px 8px; border-radius: 4px;
                            font-size: 11px; font-weight: 700;
                        ">{{ $vuln['riesgo'] }}</span>
                    </td>
                    <td style="text-align: center;">
                        @if($modoSeguro)
                            <div style="
                                display: inline-flex; align-items: center; gap: 6px;
                                background: #e8f5e9; color: #1b5e20;
                                padding: 6px 12px; border-radius: 20px;
                                font-size: 12px; font-weight: 700;
                                border: 1px solid #a5d6a7;
                            ">
                                <span style="
                                    width: 8px; height: 8px; border-radius: 50%;
                                    background: #2e7d32; display: inline-block;
                                "></span>
                                PROTEGIDO
                            </div>
                        @else
                            <div style="
                                display: inline-flex; align-items: center; gap: 6px;
                                background: #ffebee; color: #b71c1c;
                                padding: 6px 12px; border-radius: 20px;
                                font-size: 12px; font-weight: 700;
                                border: 1px solid #ffcdd2;
                                animation: pulse-red 2s infinite;
                            ">
                                <span style="
                                    width: 8px; height: 8px; border-radius: 50%;
                                    background: #c62828; display: inline-block;
                                "></span>
                                VULNERABLE
                            </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- LOG EN TIEMPO REAL --}}
<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
        <h3 style="font-size: 16px; color: #1a237e; font-weight: 700;">
            📡 Registro de Eventos en Tiempo Real
            <span style="font-size: 12px; color: #607d8b; font-weight: 400; margin-left: 10px;">
                ({{ $logs->count() }} eventos)
            </span>
        </h3>
        <div style="display: flex; gap: 10px;">
            <form method="POST" action="{{ url('/seguridad/limpiar-logs') }}" 
                  onsubmit="return confirm('⚠️ ¿Estás seguro de que quieres eliminar TODOS los logs de seguridad?\n\nEsta acción no se puede deshacer.\n\nSe eliminarán ' + {{ $logs->count() }} + ' registros.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; padding: 8px 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    🧹 Limpiar Logs
                </button>
            </form>
            
            <a href="{{ route('seguridad.logs') }}" class="btn btn-primary btn-sm">
                Ver todos los logs
            </a>
        </div>
    </div>

    @if($logs->isEmpty())
        <div style="text-align: center; padding: 32px; color: #90a4ae;">
            No hay eventos registrados aún. Interactúa con el sistema para generar eventos.
        </div>
    @else
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Vulnerabilidad</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>IP</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr style="background: {{ $log->bloqueado ? '#e8f5e9' : ($modoSeguro ? '#fff' : '#fff8f8') }}">
                        <td>
                            <span style="
                                background: #e3f2fd; color: #0d47a1;
                                padding: 2px 7px; border-radius: 4px;
                                font-size: 11px; font-weight: 700;
                            ">{{ $log->vulnerabilidad }}</span>
                        </td>
                        <td style="font-size: 12px; color: #607d8b;">{{ $log->tipo }}</td>
                        <td style="font-size: 12px; max-width: 350px;">{{ $log->descripcion }}</td>
                        <td style="font-size: 12px; font-family: monospace;">{{ $log->ip }}</td>
                        <td>
                            @if($log->bloqueado)
                                <span style="
                                    background: #e8f5e9; color: #1b5e20;
                                    padding: 2px 8px; border-radius: 10px;
                                    font-size: 11px; font-weight: 700;
                                ">✅ Bloqueado</span>
                            @else
                                <span style="
                                    background: #fff3e0; color: #e65100;
                                    padding: 2px 8px; border-radius: 10px;
                                    font-size: 11px; font-weight: 700;
                                ">⚠ Detectado</span>
                            @endif
                        </td>
                        <td style="font-size: 11px; color: #90a4ae; white-space: nowrap;">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<style>
@keyframes pulse-red {
    0%, 100% { opacity: 1; }
    50%       { opacity: .7; }
}
</style>

@endsection