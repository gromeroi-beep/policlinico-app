@extends('layouts.app')

@section('content')
<div class="page-header">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h1>📡 Registro de Eventos de Seguridad</h1>
            <p>Historial completo de ataques detectados y bloqueados por los controles NIST</p>
        </div>
        <a href="{{ route('seguridad.index') }}" class="btn btn-primary btn-sm">
            ← Volver al Panel OWASP
        </a>
    </div>
</div>

{{-- Contadores rápidos --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
    @php
        $total      = $logs->total();
        $bloqueados = \Illuminate\Support\Facades\DB::table('security_logs')->where('bloqueado', true)->count();
        $detectados = \Illuminate\Support\Facades\DB::table('security_logs')->where('bloqueado', false)->count();
        $vulns      = \Illuminate\Support\Facades\DB::table('security_logs')->distinct('vulnerabilidad')->count();
    @endphp
    <div class="card" style="text-align:center;padding:16px;border-top:4px solid #0d47a1;">
        <div style="font-size:28px;font-weight:800;color:#0d47a1;">{{ $total }}</div>
        <div style="font-size:11px;color:#607d8b;font-weight:700;text-transform:uppercase;margin-top:3px;">Total eventos</div>
    </div>
    <div class="card" style="text-align:center;padding:16px;border-top:4px solid #c62828;">
        <div style="font-size:28px;font-weight:800;color:#c62828;">{{ $detectados }}</div>
        <div style="font-size:11px;color:#607d8b;font-weight:700;text-transform:uppercase;margin-top:3px;">Detectados</div>
    </div>
    <div class="card" style="text-align:center;padding:16px;border-top:4px solid #2e7d32;">
        <div style="font-size:28px;font-weight:800;color:#2e7d32;">{{ $bloqueados }}</div>
        <div style="font-size:11px;color:#607d8b;font-weight:700;text-transform:uppercase;margin-top:3px;">Bloqueados</div>
    </div>
    <div class="card" style="text-align:center;padding:16px;border-top:4px solid #6a1b9a;">
        <div style="font-size:28px;font-weight:800;color:#6a1b9a;">{{ $vulns }}</div>
        <div style="font-size:11px;color:#607d8b;font-weight:700;text-transform:uppercase;margin-top:3px;">Tipos distintos</div>
    </div>
</div>

<div class="card">
    @if($logs->isEmpty())
        <div style="text-align:center;padding:48px;color:#90a4ae;">
            No hay eventos registrados. Interactúa con el sistema para generar eventos de seguridad.
        </div>
    @else
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th style="width:50px;">ID</th>
                    <th>Vulnerabilidad / Control NIST</th>
                    <th>Tipo de Evento</th>
                    <th>Descripción</th>
                    <th>IP</th>
                    <th style="text-align:center;width:110px;">Estado</th>
                    <th style="width:130px;">Fecha y Hora</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr style="background: {{ $log->bloqueado ? '#f1f8e9' : '#fff8f8' }}">
                    <td style="color:#90a4ae;font-weight:700;">{{ $log->id }}</td>
                    <td>
                        <span style="
                            background:#e3f2fd;color:#0d47a1;
                            padding:3px 8px;border-radius:4px;
                            font-size:11px;font-weight:700;
                        ">{{ $log->vulnerabilidad }}</span>
                    </td>
                    <td style="font-size:12px;color:#607d8b;font-family:monospace;">
                        {{ $log->tipo }}
                    </td>
                    <td style="font-size:12px;max-width:320px;line-height:1.4;">
                        {{ $log->descripcion }}
                    </td>
                    <td style="font-size:12px;font-family:monospace;color:#455a64;">
                        {{ $log->ip ?? '—' }}
                    </td>
                    <td style="text-align:center;">
                        @if($log->bloqueado)
                            <span style="
                                background:#e8f5e9;color:#1b5e20;
                                padding:3px 10px;border-radius:10px;
                                font-size:11px;font-weight:700;
                            ">✅ Bloqueado</span>
                        @else
                            <span style="
                                background:#fff3e0;color:#e65100;
                                padding:3px 10px;border-radius:10px;
                                font-size:11px;font-weight:700;
                            ">⚠ Detectado</span>
                        @endif
                    </td>
                    <td style="font-size:11px;color:#90a4ae;white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y') }}<br>
                        <strong>{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}</strong>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div style="margin-top:20px;display:flex;justify-content:center;">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection