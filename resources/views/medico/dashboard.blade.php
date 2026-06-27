@extends('layouts.medico')

@section('content')
<div class="page-header">
    <h1>📊 Dashboard</h1>
    <p>Bienvenido, Dr. {{ Auth::user()->name }} — {{ Auth::user()->especialidad->descripcion ?? 'Médico' }}</p>
</div>

{{-- CARDS DE MÉTRICAS --}}
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 28px;">

    <div class="card" style="border-top: 4px solid #0055a5; text-align:center; padding: 24px;">
        <div style="font-size: 32px; margin-bottom: 8px;">📋</div>
        <div style="font-size: 32px; font-weight: 800; color: var(--primary);">{{ $totalCitas }}</div>
        <div style="font-size: 13px; color: var(--text-light); margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Total Citas</div>
    </div>

    <div class="card" style="border-top: 4px solid #f59e0b; text-align:center; padding: 24px;">
        <div style="font-size: 32px; margin-bottom: 8px;">⏳</div>
        <div style="font-size: 32px; font-weight: 800; color: var(--warning);">{{ $citasPendientes }}</div>
        <div style="font-size: 13px; color: var(--text-light); margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Pendientes</div>
    </div>

    <div class="card" style="border-top: 4px solid #00a86b; text-align:center; padding: 24px;">
        <div style="font-size: 32px; margin-bottom: 8px;">✅</div>
        <div style="font-size: 32px; font-weight: 800; color: var(--accent);">{{ $citasAtendidas }}</div>
        <div style="font-size: 13px; color: var(--text-light); margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Atendidas</div>
    </div>

    <div class="card" style="border-top: 4px solid #0ea5e9; text-align:center; padding: 24px;">
        <div style="font-size: 32px; margin-bottom: 8px;">📅</div>
        <div style="font-size: 32px; font-weight: 800; color: #0ea5e9;">{{ $citasHoy }}</div>
        <div style="font-size: 13px; color: var(--text-light); margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Citas Hoy</div>
    </div>

</div>

{{-- PRÓXIMAS CITAS --}}
<div class="card">
    <div class="card-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        Próximas Citas Pendientes
    </div>

    @if($proximasCitas->isEmpty())
        <div class="alert alert-warning">No tiene citas pendientes próximas.</div>
    @else
    <table class="table">
        <thead>
            <tr>
                <th>Paciente</th>
                <th>Especialidad</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proximasCitas as $cita)
            <tr>
                {{-- MITIGACIÓN XSS: {{ }} escapa automáticamente --}}
                <td><strong>{{ $cita->paciente->name ?? '-' }}</strong></td>
                <td>{{ $cita->especialidad->descripcion ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($cita->fecha_cita)->format('d/m/Y') }}</td>
                <td>{{ $cita->hora_cita }}</td>
                <td><span class="badge badge-warning">{{ $cita->estado }}</span></td>
                <td>
                    <a href="{{ route('medico.citas') }}" class="btn btn-primary"
                        style="padding: 6px 14px; font-size: 12px;">
                        Ver Citas
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection