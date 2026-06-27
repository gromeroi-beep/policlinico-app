@extends('layouts.medico')

@section('content')
@php
    try {
        $modoSeguro = (bool) \Illuminate\Support\Facades\DB::table('security_settings')
            ->where('clave', 'modo_seguro')->value('valor');
    } catch (\Exception $e) {
        $modoSeguro = true;
    }
@endphp

<div class="page-header">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>📋 Historial Clínico</h1>
            <p>Ficha médica del paciente</p>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
            <a href="{{ route('medico.historiales') }}" class="btn btn-secondary">← Volver</a>
            @if($historial)
                <a href="{{ route('medico.historiales.edit', $paciente->id) }}" class="btn btn-warning">
                    ✏️ Editar
                </a>
                <form method="POST" action="{{ route('medico.historiales.destroy', $paciente->id) }}" 
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
        🔴 MODO INSEGURO ACTIVO — Vulnerabilidad #2 (XSS) expuesta
    </div>
    <div style="font-size:12px; color:#c62828; line-height:1.6;">
        <strong>XSS (NIST SI-10 + SC-18):</strong>
        Los campos de diagnóstico, alergias y antecedentes NO están escapados.
        Un atacante puede inyectar <code style="background:#ffcdd2;padding:1px 5px;border-radius:3px;">&lt;script&gt;alert('XSS')&lt;/script&gt;</code>
        y se ejecutará al ver el historial.
    </div>
</div>
@else
<div style="background:#e8f5e9; border:1.5px solid #a5d6a7; border-left:5px solid #2e7d32; border-radius:8px; padding:14px 18px; margin-bottom:20px;">
    <div style="font-weight:700; color:#1b5e20; font-size:14px; margin-bottom:4px;">
        🟢 MODO SEGURO — Control NIST SI-10 + SC-18 Activo
    </div>
    <div style="font-size:12px; color:#2e7d32;">
        XSS protegido con <code style="background:#c8e6c9;padding:1px 5px;border-radius:3px;">{{ }}</code>
        — Todo el HTML es escapado automáticamente.
    </div>
</div>
@endif

<div style="display:grid; grid-template-columns:1fr 2fr; gap:24px; align-items:start;">

    {{-- DATOS PERSONALES --}}
    <div class="card">
        <div class="card-title">👤 Datos del Paciente</div>

        <div style="text-align:center; margin-bottom:20px;">
            <div style="width:72px; height:72px;
                background:linear-gradient(135deg,var(--primary-light),var(--primary));
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
            <div style="display:flex; justify-content:space-between; padding:10px 14px;
                background:var(--bg); border-radius:var(--radius-sm);">
                <span style="font-size:12px; color:var(--text-mid); font-weight:600; text-transform:uppercase;">Código</span>
                <span style="font-size:13px; font-weight:700; color:var(--primary);">{{ $historial->codigo_historial }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 14px;
                background:var(--bg); border-radius:var(--radius-sm);">
                <span style="font-size:12px; color:var(--text-mid); font-weight:600; text-transform:uppercase;">Grupo Sanguíneo</span>
                <span class="badge badge-danger">{{ $historial->grupo_sanguineo ?? 'No registrado' }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 14px;
                background:var(--bg); border-radius:var(--radius-sm);">
                <span style="font-size:12px; color:var(--text-mid); font-weight:600; text-transform:uppercase;">Estado</span>
                <span class="badge
                    {{ $historial->estado_paciente == 'Estable' ? 'badge-success' : '' }}
                    {{ $historial->estado_paciente == 'En Tratamiento' ? 'badge-warning' : '' }}
                    {{ $historial->estado_paciente == 'Crítico' ? 'badge-danger'  : '' }}
                ">{{ $historial->estado_paciente }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 14px;
                background:var(--bg); border-radius:var(--radius-sm);">
                <span style="font-size:12px; color:var(--text-mid); font-weight:600; text-transform:uppercase;">Actualizado</span>
                <span style="font-size:12px; color:var(--text-mid);">{{ $historial->updated_at->format('d/m/Y') }}</span>
            </div>
        </div>
        @else
            <div class="alert alert-warning">Sin historial registrado.</div>
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
                        @if(str_contains($historial->diagnostico_principal ?? '', '<script>'))
                            <div style="background:#ffebee; color:#c62828; padding:8px 12px; border-radius:4px; font-size:12px; font-weight:600; margin-top:8px;">
                                ⚠️ XSS DETECTADO — Se ejecutó código JavaScript en el navegador
                            </div>
                        @endif
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
                        {!! $historial->observaciones ?? 'Sin observaciones' !!}
                    @else
                        {{ $historial->observaciones ?? 'Sin observaciones' }}
                    @endif
                </p>
            </div>
        @else
            <div class="card">
                <div class="alert alert-warning">
                    ⚠️ Este paciente no tiene historial registrado. Puede crearlo desde el panel izquierdo.
                </div>
            </div>
        @endif
    </div>
</div>
@endsection