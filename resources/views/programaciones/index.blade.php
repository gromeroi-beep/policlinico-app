@extends('layouts.app')

@php
    // 🔥 FORZAR RECARGA DE DATOS - TIMESTAMP PARA EVITAR CACHÉ
    $timestamp = time();
@endphp

@section('content')
<div class="page-header">
    <h1>📅 Programación Médica</h1>
    <p>Defina los horarios de atención de los médicos</p>
</div>

<div style="display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;">

    {{-- FORMULARIO --}}
    <div class="card">
        <div class="card-title">➕ Nueva Programación</div>

        <form method="POST" action="{{ route('programaciones.store') }}">
            @csrf

            <div class="form-group">
                <label>Especialidad</label>
                <select name="especialidad_id" id="especialidad_id" class="form-control"
                    onchange="cargarMedicos()" required>
                    <option value="">-- Seleccione especialidad --</option>
                    @foreach($especialidades as $esp)
                        <option value="{{ $esp->id }}" {{ old('especialidad_id')==$esp->id ? 'selected' : '' }}>
                            {{ $esp->descripcion }}
                        </option>
                    @endforeach
                </select>
                @error('especialidad_id')<small style="color:#ef4444;">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label>Médico</label>
                <select name="user_id" id="user_id" class="form-control" disabled required>
                    <option value="">-- Primero seleccione especialidad --</option>
                </select>
                @error('user_id')<small style="color:#ef4444;">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label>Fecha</label>
                <input type="date" name="fecha" class="form-control"
                    value="{{ old('fecha') }}" required>
                @error('fecha')<small style="color:#ef4444;">{{ $message }}</small>@enderror
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Hora Inicio</label>
                    <input type="time" name="hora_inicio" class="form-control"
                        value="{{ old('hora_inicio') }}" required>
                    @error('hora_inicio')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label>Hora Fin</label>
                    <input type="time" name="hora_fin" class="form-control"
                        value="{{ old('hora_fin') }}" required>
                    @error('hora_fin')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">
                💾 Guardar Programación
            </button>
        </form>
    </div>

    {{-- TABLA --}}
    <div class="card">
        <div class="card-title">
            📋 Programaciones Registradas
            <span style="margin-left:auto;font-size:11px;color:#999;">
                🔄 Actualizado: {{ date('H:i:s') }}
            </span>
        </div>

        @if($programaciones->isEmpty())
            <div class="alert alert-warning">No hay programaciones registradas aún.</div>
        @else
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Especialidad</th>
                    <th>Médico</th>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($programaciones as $prog)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $prog->especialidad->descripcion ?? '-' }}</td>
                    <td>{{ $prog->medico->name ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($prog->fecha)->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge badge-success">
                            {{ $prog->hora_inicio }} - {{ $prog->hora_fin }}
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('programaciones.destroy', $prog->id) }}"
                            onsubmit="return confirm('¿Eliminar esta programación?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"
                                style="padding:6px 12px; font-size:12px;">
                                🗑️ Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- 🔥 TIMESTAMP OCULTO PARA FORZAR RECARGA --}}
        <!-- Última actualización: {{ $timestamp }} -->
    </div>
</div>

<script>
// AJAX: Cargar médicos según especialidad seleccionada
function cargarMedicos() {
    const especialidadId = document.getElementById('especialidad_id').value;
    const selectMedico   = document.getElementById('user_id');

    if (!especialidadId) {
        selectMedico.disabled = true;
        selectMedico.innerHTML = '<option value="">-- Primero seleccione especialidad --</option>';
        return;
    }

    fetch(`/api/medicos/${especialidadId}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(medicos => {
        selectMedico.disabled = false;
        selectMedico.innerHTML = '<option value="">-- Seleccione médico --</option>';
        if (medicos.length === 0) {
            selectMedico.innerHTML = '<option value="">Sin médicos en esta especialidad</option>';
            selectMedico.disabled = true;
            return;
        }
        medicos.forEach(m => {
            selectMedico.innerHTML += `<option value="${m.id}">${m.name} (${m.colegiatura})</option>`;
        });
    })
    .catch(() => {
        selectMedico.innerHTML = '<option value="">Error al cargar médicos</option>';
    });
}
</script>
@endsection