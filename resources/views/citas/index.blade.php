@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>📋 Registro de Citas</h1>
    <p>Gestione las citas médicas del policlínico</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

    {{-- COLUMNA IZQUIERDA: Formulario --}}
    <div>
        <div class="card">
            <div class="card-title">➕ Nueva Cita</div>
            <form method="POST" action="{{ route('citas.store') }}">
                @csrf

                {{-- BÚSQUEDA DE PACIENTE --}}
                <div class="card-title" style="font-size:13px; color:#6b7280;">
                    🔍 Buscar Paciente por Documento
                </div>

                <div style="display:grid; grid-template-columns:120px 1fr auto; gap:8px; margin-bottom:16px;">
                    <select id="tipo_doc" name="tipo_doc" class="form-control">
                        <option value="DNI">DNI</option>
                        <option value="CE">CE</option>
                    </select>
                    <input type="text" id="num_doc" name="num_doc" class="form-control"
                        placeholder="Número de documento">
                    <button type="button" onclick="buscarPaciente()"
                        class="btn btn-secondary">🔍 Buscar</button>
                </div>

                <div class="form-group">
                    <label>Nombre del Paciente</label>
                    <input type="text" id="paciente_nombre" name="paciente_nombre"
                        class="form-control" placeholder="Se autocompletará o ingrese manualmente">
                    @error('paciente_nombre')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                </div>

                <div id="mensaje-paciente" style="margin-bottom:12px;"></div>

                <hr style="margin: 16px 0; border-color:#f0f4f8;">

                <div class="form-group">
                    <label>Especialidad</label>
                    <select name="especialidad_id" id="esp_cita" class="form-control"
                        onchange="cargarMedicosCita()" required>
                        <option value="">-- Seleccione --</option>
                        @foreach($especialidades as $esp)
                            <option value="{{ $esp->id }}">{{ $esp->descripcion }}</option>
                        @endforeach
                    </select>
                    @error('especialidad_id')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label>Médico</label>
                    <select name="medico_id" id="medico_cita" class="form-control"
                        onchange="cargarDisponibilidad()" disabled required>
                        <option value="">-- Seleccione especialidad primero --</option>
                    </select>
                    @error('medico_id')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label>Fecha de Cita</label>
                        <input type="date" name="fecha_cita" class="form-control" required>
                        @error('fecha_cita')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>Hora de Cita</label>
                        <input type="time" name="hora_cita" class="form-control" required>
                        @error('hora_cita')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">
                    💾 Registrar Cita
                </button>
            </form>
        </div>
    </div>

    {{-- COLUMNA DERECHA: Disponibilidad --}}
    <div>
        <div class="card">
            <div class="card-title">🕐 Disponibilidad del Médico</div>
            <div id="panel-disponibilidad">
                <div class="alert alert-warning">
                    Seleccione un médico para ver su disponibilidad.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABLA DE CITAS --}}
<div class="card" style="margin-top:24px;">
    <div class="card-title">📋 Citas Registradas</div>

    @if($citas->isEmpty())
        <div class="alert alert-warning">No hay citas registradas aún.</div>
    @else
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Paciente</th>
                <th>Especialidad</th>
                <th>Médico</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($citas as $cita)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $cita->paciente->name ?? '-' }}</td>
                <td>{{ $cita->especialidad->descripcion ?? '-' }}</td>
                <td>{{ $cita->medico->name ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($cita->fecha_cita)->format('d/m/Y') }}</td>
                <td>{{ $cita->hora_cita }}</td>
                <td>
                    <span class="badge
                        {{ $cita->estado=='Pendiente' ? 'badge-warning' : '' }}
                        {{ $cita->estado=='Atendida'  ? 'badge-success' : '' }}
                        {{ $cita->estado=='Cancelada' ? 'badge-danger'  : '' }}">
                        {{ $cita->estado }}
                    </span>
                </td>
                <td style="display:flex; gap:6px;">
                    {{-- Cambiar estado --}}
                    <form method="POST" action="{{ route('citas.update', $cita->id) }}">
                        @csrf
                        @method('PUT')
                        <select name="estado" class="form-control"
                            style="padding:4px 8px; font-size:12px; width:120px;"
                            onchange="this.form.submit()">
                            <option value="Pendiente"  {{ $cita->estado=='Pendiente'  ? 'selected':'' }}>Pendiente</option>
                            <option value="Atendida"   {{ $cita->estado=='Atendida'   ? 'selected':'' }}>Atendida</option>
                            <option value="Cancelada"  {{ $cita->estado=='Cancelada'  ? 'selected':'' }}>Cancelada</option>
                        </select>
                    </form>
                    {{-- Eliminar --}}
                    <form method="POST" action="{{ route('citas.destroy', $cita->id) }}"
                        onsubmit="return confirm('¿Eliminar esta cita?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                            style="padding:6px 10px; font-size:12px;">🗑️</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// RF-006: Búsqueda asíncrona de paciente
function buscarPaciente() {
    const numDoc = document.getElementById('num_doc').value.trim();
    const tipo   = document.getElementById('tipo_doc').value;
    const msg    = document.getElementById('mensaje-paciente');

    if (!numDoc) { alert('Ingrese un número de documento'); return; }

    fetch(`/api/paciente/${numDoc}`, {
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.encontrado) {
            document.getElementById('paciente_nombre').value = data.paciente.name;
            document.getElementById('tipo_doc').value        = data.paciente.tipo_doc;
            msg.innerHTML = '<div class="alert alert-success">✅ Paciente encontrado y autocompletado.</div>';
        } else {
            document.getElementById('paciente_nombre').value = '';
            msg.innerHTML = '<div class="alert alert-warning">⚠️ Paciente no encontrado. Complete los datos para registrarlo.</div>';
        }
    });
}

// Cargar médicos por especialidad
function cargarMedicosCita() {
    const espId  = document.getElementById('esp_cita').value;
    const select = document.getElementById('medico_cita');

    if (!espId) { select.disabled = true; return; }

    fetch(`/api/medicos/${espId}`, {
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(medicos => {
        select.disabled = false;
        select.innerHTML = '<option value="">-- Seleccione médico --</option>';
        medicos.forEach(m => {
            select.innerHTML += `<option value="${m.id}">${m.name}</option>`;
        });
    });
}

// RF-008: Cargar disponibilidad del médico
function cargarDisponibilidad() {
    const medicoId = document.getElementById('medico_cita').value;
    const panel    = document.getElementById('panel-disponibilidad');

    if (!medicoId) return;

    panel.innerHTML = '<p style="color:#6b7280;">Cargando...</p>';

    fetch(`/api/disponibilidad/${medicoId}`, {
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(programaciones => {
        if (programaciones.length === 0) {
            panel.innerHTML = '<div class="alert alert-warning">Sin programaciones disponibles.</div>';
            return;
        }
        let html = '<table class="table"><thead><tr><th>Fecha</th><th>Horario</th><th>Especialidad</th></tr></thead><tbody>';
        programaciones.forEach(p => {
            html += `<tr>
                <td>${p.fecha}</td>
                <td><span class="badge badge-success">${p.hora_inicio} - ${p.hora_fin}</span></td>
                <td>${p.especialidad ? p.especialidad.descripcion : '-'}</td>
            </tr>`;
        });
        html += '</tbody></table>';
        panel.innerHTML = html;
    });
}
</script>
@endsection