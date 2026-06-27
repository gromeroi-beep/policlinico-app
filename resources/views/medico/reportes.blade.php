@extends('layouts.medico')

@section('content')
<div class="page-header">
    <h1>📈 Mis Reportes</h1>
    <p>Reporte de sus consultas médicas</p>
</div>

{{-- FILTROS --}}
<div class="card">
    <div class="card-title">🔍 Filtros de Búsqueda</div>
    <form method="GET" action="{{ route('medico.reportes') }}">
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:16px; align-items:end;">

            <div class="form-group" style="margin-bottom:0;">
                <label>Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control"
                    value="{{ request('fecha_inicio') }}">
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label>Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control"
                    value="{{ request('fecha_fin') }}">
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="">-- Todos --</option>
                    <option value="Pendiente"  {{ request('estado') == 'Pendiente'  ? 'selected' : '' }}>Pendiente</option>
                    <option value="Atendida"   {{ request('estado') == 'Atendida'   ? 'selected' : '' }}>Atendida</option>
                    <option value="Cancelada"  {{ request('estado') == 'Cancelada'  ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>

            <div style="display:flex; gap:8px; padding-bottom:0;">
                <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
                <a href="{{ route('medico.reportes') }}" class="btn btn-secondary">🔄</a>
            </div>
        </div>
    </form>
</div>

{{-- RESULTADOS --}}
<div class="card">
    <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
        <div style="display:flex; align-items:center; gap:8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Resultados ({{ $citas->count() }} citas)
        </div>

        <div style="display:flex; gap:10px;">
            <a href="{{ route('medico.reportes.excel') }}?{{ http_build_query(request()->all()) }}"
                class="btn btn-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exportar Excel
            </a>
            <a href="{{ route('medico.reportes.pdf') }}?{{ http_build_query(request()->all()) }}"
                class="btn btn-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exportar PDF
            </a>
        </div>
    </div>

    @if($citas->isEmpty())
        <div class="alert alert-warning">No se encontraron citas con los filtros aplicados.</div>
    @else
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Paciente</th>
                <th>Documento</th>
                <th>Especialidad</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Estado</th>
                <th>Diagnóstico</th>
                <th>Estado Clínico</th>
            </tr>
        </thead>
        <tbody>
            @foreach($citas as $cita)
            <tr>
                <td>{{ $loop->iteration }}</td>
                {{-- MITIGACIÓN XSS: {{ }} escapa automáticamente --}}
                <td>{{ $cita->paciente->name ?? '-' }}</td>
                <td>{{ ($cita->paciente->tipo_doc ?? '') }}: {{ ($cita->paciente->num_doc ?? '-') }}</td>
                <td>{{ $cita->especialidad->descripcion ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($cita->fecha_cita)->format('d/m/Y') }}</td>
                <td>{{ $cita->hora_cita }}</td>
                <td>
                    <span class="badge
                        {{ $cita->estado == 'Pendiente' ? 'badge-warning' : '' }}
                        {{ $cita->estado == 'Atendida'  ? 'badge-success' : '' }}
                        {{ $cita->estado == 'Cancelada' ? 'badge-danger'  : '' }}">
                        {{ $cita->estado }}
                    </span>
                </td>
                <td>{{ $cita->paciente->historialClinico->diagnostico_principal ?? 'Sin historial' }}</td>
                <td>
                    @if($cita->paciente->historialClinico)
                        @php $est = $cita->paciente->historialClinico->estado_paciente; @endphp
                        <span class="badge
                            {{ $est == 'Estable'        ? 'badge-success' : '' }}
                            {{ $est == 'En Tratamiento' ? 'badge-warning' : '' }}
                            {{ $est == 'Crítico'        ? 'badge-danger'  : '' }}">
                            {{ $est }}
                        </span>
                    @else
                        <span class="badge badge-primary">Sin datos</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection