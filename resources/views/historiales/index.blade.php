@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>🏥 Historial Clínico</h1>
    <p>Consulte y gestione los historiales clínicos de los pacientes</p>
</div>

<div style="display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;">

    {{-- BUSCADOR --}}
    <div class="card">
        <div class="card-title">🔍 Buscar Paciente</div>

        {{-- MITIGACIÓN CSRF --}}
        <form method="POST" action="{{ route('historiales.buscar') }}">
            @csrf
            <div class="form-group">
                <label>Número de Documento</label>
                <input type="text" name="num_doc" class="form-control"
                    value="{{ old('num_doc') }}"
                    placeholder="Ingrese DNI o CE del paciente"
                    required>
                @error('num_doc')
                    <small style="color:var(--danger); font-weight:500; margin-top:4px; display:block;">
                        {{ $message }}
                    </small>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                🔍 Buscar Historial
            </button>
        </form>

        {{-- SEPARADOR --}}
        <div style="border-top:2px solid var(--bg); margin:24px 0;"></div>

        {{-- NUEVO HISTORIAL --}}
        <div class="card-title">➕ Registrar Historial</div>
        <form method="POST" action="{{ route('historiales.store') }}">
            @csrf

            <div class="form-group">
                <label>Paciente</label>
                <select name="paciente_id" class="form-control" required>
                    <option value="">-- Seleccione paciente --</option>
                    @foreach($pacientes as $paciente)
                        <option value="{{ $paciente->id }}">
                            {{ $paciente->name }} — {{ $paciente->tipo_doc }}: {{ $paciente->num_doc }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Grupo Sanguíneo</label>
                    <select name="grupo_sanguineo" class="form-control">
                        <option value="">-- Seleccione --</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $grupo)
                            <option value="{{ $grupo }}">{{ $grupo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Estado del Paciente</label>
                    <select name="estado_paciente" class="form-control" required>
                        <option value="Estable">Estable</option>
                        <option value="En Tratamiento">En Tratamiento</option>
                        <option value="Crítico">Crítico</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Alergias</label>
                <textarea name="alergias" class="form-control"
                    rows="2" placeholder="Ej: Penicilina, Polen..."></textarea>
            </div>

            <div class="form-group">
                <label>Diagnóstico Principal</label>
                <textarea name="diagnostico_principal" class="form-control"
                    rows="2" placeholder="Diagnóstico médico principal..."></textarea>
            </div>

            <div class="form-group">
                <label>Antecedentes Médicos</label>
                <textarea name="antecedentes_medicos" class="form-control"
                    rows="2" placeholder="Diabetes, Hipertensión..."></textarea>
            </div>

            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones" class="form-control"
                    rows="2" placeholder="Observaciones adicionales..."></textarea>
            </div>

            <button type="submit" class="btn btn-success" style="width:100%; justify-content:center;">
                💾 Guardar Historial
            </button>
        </form>
    </div>

    {{-- TABLA DE PACIENTES --}}
    <div class="card">
        <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
            <span>👥 Pacientes Registrados</span>
            <span class="badge badge-primary" style="font-size:13px;">
                Total: {{ $pacientes->count() }}
            </span>
        </div>

        @if($pacientes->isEmpty())
            <div class="alert alert-warning">No hay pacientes registrados aún.</div>
        @else
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Paciente</th>
                    <th>Documento</th>
                    <th>Historial</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pacientes as $paciente)
                <tr>
                    <td><strong>{{ $loop->iteration }}</strong></td>
                    {{-- MITIGACIÓN XSS: {{ }} escapa automáticamente --}}
                    <td>{{ $paciente->name }}</td>
                    <td>
                        <span class="badge badge-primary">{{ $paciente->tipo_doc }}</span>
                        {{ $paciente->num_doc }}
                    </td>
                    <td>
                        @if($paciente->historialClinico)
                            <span class="badge badge-success">✅ Registrado</span>
                        @else
                            <span class="badge badge-warning">⚠️ Sin historial</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('historiales.buscar') }}" style="margin:0;">
                            @csrf
                            <input type="hidden" name="num_doc" value="{{ $paciente->num_doc }}">
                            <button type="submit" class="btn btn-primary"
                                style="padding:6px 14px; font-size:12px;">
                                👁️ Ver Historial
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection