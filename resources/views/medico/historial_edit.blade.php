@extends('layouts.medico')

@section('content')
@php
    $modoSeguro = (bool) \Illuminate\Support\Facades\DB::table('security_settings')
        ->where('clave', 'modo_seguro')->value('valor');
@endphp

<div class="page-header">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>✏️ Editar Historial Clínico</h1>
            <p>Actualice la ficha médica de {{ $paciente->name }}</p>
        </div>
        <a href="{{ route('medico.historiales.show', $paciente->id) }}" class="btn btn-secondary">
            ← Volver
        </a>
    </div>
</div>

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
</div>
@endif

<div class="card" style="max-width:800px; margin:0 auto;">
    <form method="POST" action="{{ route('medico.historiales.update', $paciente->id) }}">
        @csrf
        @method('PUT')

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="form-group">
                <label>Grupo Sanguíneo</label>
                <select name="grupo_sanguineo" class="form-control">
                    <option value="">-- Seleccione --</option>
                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $grupo)
                        <option value="{{ $grupo }}" {{ old('grupo_sanguineo', $historial->grupo_sanguineo) == $grupo ? 'selected' : '' }}>
                            {{ $grupo }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Estado del Paciente</label>
                <select name="estado_paciente" class="form-control" required>
                    <option value="Estable" {{ old('estado_paciente', $historial->estado_paciente) == 'Estable' ? 'selected' : '' }}>Estable</option>
                    <option value="En Tratamiento" {{ old('estado_paciente', $historial->estado_paciente) == 'En Tratamiento' ? 'selected' : '' }}>En Tratamiento</option>
                    <option value="Crítico" {{ old('estado_paciente', $historial->estado_paciente) == 'Crítico' ? 'selected' : '' }}>Crítico</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Alergias</label>
            <textarea name="alergias" class="form-control" rows="3">{{ old('alergias', $historial->alergias) }}</textarea>
        </div>

        <div class="form-group">
            <label>Diagnóstico Principal</label>
            <textarea name="diagnostico_principal" class="form-control" rows="3">{{ old('diagnostico_principal', $historial->diagnostico_principal) }}</textarea>
        </div>

        <div class="form-group">
            <label>Antecedentes Médicos</label>
            <textarea name="antecedentes_medicos" class="form-control" rows="3">{{ old('antecedentes_medicos', $historial->antecedentes_medicos) }}</textarea>
        </div>

        <div class="form-group">
            <label>Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones', $historial->observaciones) }}</textarea>
        </div>

        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">
                💾 Actualizar Historial
            </button>
            <a href="{{ route('medico.historiales.show', $paciente->id) }}" class="btn btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection