@extends('layouts.app')

@php
    try {
        $modoSeguro = (bool) \Illuminate\Support\Facades\DB::table('security_settings')
            ->where('clave', 'modo_seguro')->value('valor');
    } catch (\Exception $e) {
        $modoSeguro = true;
    }
@endphp

@section('content')
<div class="page-header">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>📋 Historial Clínico</h1>
            <p>Ficha médica completa del paciente</p>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
            <a href="{{ route('historiales.index') }}" class="btn btn-secondary">
                ← Volver
            </a>
            @if($historial)
                <a href="{{ route('historiales.edit', $paciente->id) }}" class="btn btn-warning">
                    ✏️ Editar
                </a>
                <form method="POST" action="{{ route('historiales.destroy', $paciente->id) }}" 
                      onsubmit="return confirm('¿Eliminar este historial clínico?')" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        🗑️ Eliminar
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

{{-- BANNER DE MODO SEGURIDAD --}}
@if(!$modoSeguro)
<div style="background:#ffebee; border:1.5px solid #ef9a9a; border-left:5px solid #c62828; border-radius:8px; padding:14px 18px; margin-bottom:20px;">
    <div style="font-weight:700; color:#b71c1c; font-size:14px; margin-bottom:6px;">
        🔴 MODO INSEGURO ACTIVO — XSS EXPUESTO
    </div>
    <div style="font-size:12px; color:#c62828; line-height:1.6;">
        Los campos de diagnóstico, alergias y antecedentes NO están escapados.
    </div>
</div>
@else
<div style="background:#e8f5e9; border:1.5px solid #a5d6a7; border-left:5px solid #2e7d32; border-radius:8px; padding:14px 18px; margin-bottom:20px;">
    <div style="font-weight:700; color:#1b5e20; font-size:14px; margin-bottom:4px;">
        🟢 MODO SEGURO — XSS PROTEGIDO
    </div>
    <div style="font-size:12px; color:#2e7d32;">
        Todo el HTML es escapado automáticamente.
    </div>
</div>
@endif

{{-- FICHA DEL PACIENTE --}}
<div style="display:grid; grid-template-columns:1fr 2fr; gap:24px; align-items:start;">

    {{-- DATOS PERSONALES --}}
    <div class="card">
        <div class="card-title">👤 Datos del Paciente</div>

        <div style="text-align:center; margin-bottom:20px;">
            <div style="width:72px; height:72px; background:linear-gradient(135deg,var(--primary-light),var(--primary));
                border-radius:50%; display:flex; align-items:center; justify-content:center;
                font-size:28px; font-weight:800; color:#fff; margin:0 auto 12px;">
                {{ strtoupper(substr($paciente->name, 0, 1)) }}
            </div>
            <h3 style="color:var(--primary); font-size:16px; font-weight:700;">
                {{ $paciente->name }}
            </h3>
            <span class="badge badge-primary" style="margin-top:4px;">
                {{ $paciente->tipo_doc }}: {{ $paciente->num_doc }}
            </span>
        </div>

        @if($historial)
        <div style="display:flex; flex-direction:column; gap:12px;">
            <div style="display:flex; justify-content:space-between; padding:10px 14px; background:var(--bg); border-radius:var(--radius-sm);">
                <span style="font-size:12px; color:var(--text-mid); font-weight:600; text-transform:uppercase;">Código Historial</span>
                <span style="font-size:13px; font-weight:700; color:var(--primary);">{{ $historial->codigo_historial }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 14px; background:var(--bg); border-radius:var(--radius-sm);">
                <span style="font-size:12px; color:var(--text-mid); font-weight:600; text-transform:uppercase;">Grupo Sanguíneo</span>
                <span class="badge badge-danger" style="font-size:13px;">{{ $historial->grupo_sanguineo ?? 'No registrado' }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 14px; background:var(--bg); border-radius:var(--radius-sm);">
                <span style="font-size:12px; color:var(--text-mid); font-weight:600; text-transform:uppercase;">Estado</span>
                <span class="badge
                    {{ $historial->estado_paciente == 'Estable' ? 'badge-success' : '' }}
                    {{ $historial->estado_paciente == 'En Tratamiento' ? 'badge-warning' : '' }}
                    {{ $historial->estado_paciente == 'Critico' ? 'badge-danger' : '' }}
                ">{{ $historial->estado_paciente }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 14px; background:var(--bg); border-radius:var(--radius-sm);">
                <span style="font-size:12px; color:var(--text-mid); font-weight:600; text-transform:uppercase;">Última actualización</span>
                <span style="font-size:12px; color:var(--text-mid);">{{ $historial->updated_at->format('d/m/Y') }}</span>
            </div>
        </div>
        @else
            <div class="alert alert-warning">⚠️ Este paciente no tiene historial clínico registrado aún.</div>
        @endif
    </div>

    {{-- HISTORIAL MÉDICO --}}
    <div>
        @if($historial)

        {{-- ALERGIAS --}}
        <div class="card" style="border-left:4px solid var(--danger);">
            <div class="card-title">⚠️ Alergias</div>
            <p style="color:var(--text-dark); font-size:14px; line-height:1.6;">
                @if(!$modoSeguro)
                    {!! $historial->alergias ?? 'Ninguna registrada' !!}
                @else
                    {{ $historial->alergias ?? 'Ninguna registrada' }}
                @endif
            </p>
        </div>

        {{-- DIAGNÓSTICO --}}
        <div class="card" style="border-left:4px solid var(--primary);">
            <div class="card-title">🩺 Diagnóstico Principal</div>
            <p style="color:var(--text-dark); font-size:14px; line-height:1.6;">
                @if(!$modoSeguro)
                    {!! $historial->diagnostico_principal ?? 'Sin diagnóstico registrado' !!}
                @else
                    {{ $historial->diagnostico_principal ?? 'Sin diagnóstico registrado' }}
                @endif
            </p>
        </div>

        {{-- ANTECEDENTES --}}
        <div class="card" style="border-left:4px solid var(--warning);">
            <div class="card-title">📋 Antecedentes Médicos</div>
            <p style="color:var(--text-dark); font-size:14px; line-height:1.6;">
                @if(!$modoSeguro)
                    {!! $historial->antecedentes_medicos ?? 'Sin antecedentes registrados' !!}
                @else
                    {{ $historial->antecedentes_medicos ?? 'Sin antecedentes registrados' }}
                @endif
            </p>
        </div>

        {{-- OBSERVACIONES --}}
        <div class="card" style="border-left:4px solid var(--accent);">
            <div class="card-title">📝 Observaciones</div>
            <p style="color:var(--text-dark); font-size:14px; line-height:1.6;">
                @if(!$modoSeguro)
                    {!! $historial->observaciones ?? 'Sin observaciones registradas' !!}
                @else
                    {{ $historial->observaciones ?? 'Sin observaciones registradas' }}
                @endif
            </p>
        </div>

        @else
            <div class="alert alert-warning">⚠️ Este paciente no tiene historial clínico registrado aún.</div>
        @endif
    </div>
</div>
@endsection