@extends('layouts.app')

@section('content')
<div class="page-header">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>✏️ Editar Historial Clínico</h1>
            <p>Actualice la ficha médica de {{ $paciente->name }}</p>
        </div>
        <a href="{{ route('historiales.show', $paciente->id) }}" class="btn btn-secondary">
            ← Volver
        </a>
    </div>
</div>

<div class="card" style="max-width:800px; margin:0 auto;">
    <form method="POST" action="{{ route('historiales.update', $paciente->id) }}">
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
            <a href="{{ route('historiales.show', $paciente->id) }}" class="btn btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection