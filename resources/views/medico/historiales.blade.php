@extends('layouts.medico')

@section('content')
<div class="page-header">
    <h1>📁 Historial Clínico</h1>
    <p>Gestione los historiales de sus pacientes atendidos</p>
</div>

<div style="display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;">

    {{-- FORMULARIO IZQUIERDA --}}
    <div>
        {{-- BUSCADOR --}}
        <div class="card">
            <div class="card-title">🔍 Buscar Paciente</div>
            <form method="POST" action="{{ route('medico.historiales.buscar') }}">
                @csrf
                <div class="form-group">
                    <label>Número de Documento</label>
                    <input type="text" name="num_doc" class="form-control"
                        value="{{ old('num_doc') }}"
                        placeholder="DNI o CE del paciente"
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
        </div>

        {{-- NUEVO HISTORIAL --}}
        <div class="card">
            <div class="card-title">➕ Registrar Historial</div>
            <form method="POST" action="{{ route('medico.historiales.store') }}">
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
                    <textarea name="alergias" class="form-control" rows="2"
                        placeholder="Ej: Penicilina, Polen..."></textarea>
                </div>

                <div class="form-group">
                    <label>Diagnóstico Principal</label>
                    <textarea name="diagnostico_principal" class="form-control" rows="2"
                        placeholder="Diagnóstico médico principal..."></textarea>
                </div>

                <div class="form-group">
                    <label>Antecedentes Médicos</label>
                    <textarea name="antecedentes_medicos" class="form-control" rows="2"
                        placeholder="Diabetes, Hipertensión..."></textarea>
                </div>

                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2"
                        placeholder="Observaciones adicionales..."></textarea>
                </div>

                <button type="submit" class="btn btn-success" style="width:100%; justify-content:center;">
                    💾 Guardar Historial
                </button>
            </form>
        </div>
    </div>

    {{-- TABLA DERECHA --}}
    <div class="card">
        <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
            <span>👥 Mis Pacientes Atendidos</span>
            <span class="badge badge-primary" style="font-size:13px;">
                Total: {{ $pacientes->count() }}
            </span>
        </div>

        @if($pacientes->isEmpty())
            <div class="alert alert-warning">No tiene pacientes atendidos aún.</div>
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
                        <a href="{{ route('medico.historiales.show', $paciente->id) }}"
                            class="btn btn-primary" style="padding:6px 14px; font-size:12px;">
                            👁️ Ver Historial
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection