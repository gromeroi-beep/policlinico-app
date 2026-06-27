@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Reportes de Citas
    </h1>
    <p>Filtra y exporta el historial de citas médicas con información clínica</p>
</div>

<div class="card">
    <div class="card-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        Filtros de Búsqueda
    </div>
    
    <form method="GET" action="{{ route('reportes.index') }}" id="filtrosForm">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div class="form-group">
                <label>Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
            </div>
            
            <div class="form-group">
                <label>Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
            </div>
            
            <div class="form-group">
                <label>Especialidad</label>
                <select name="especialidad_id" class="form-control">
                    <option value="">Todas</option>
                    @foreach($especialidades as $especialidad)
                        <option value="{{ $especialidad->id }}" {{ request('especialidad_id') == $especialidad->id ? 'selected' : '' }}>
                            {{ $especialidad->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label>Médico</label>
                <select name="medico_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach($medicos as $medico)
                        <option value="{{ $medico->id }}" {{ request('medico_id') == $medico->id ? 'selected' : '' }}>
                            {{ $medico->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label>Estado de Cita</label>
                <select name="estado" class="form-control">
                    <option value="">Todos</option>
                    <option value="Pendiente" {{ request('estado') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="Atendida" {{ request('estado') == 'Atendida' ? 'selected' : '' }}>Atendida</option>
                    <option value="Cancelada" {{ request('estado') == 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
            
            <div class="form-group" style="display: flex; align-items: flex-end; gap: 10px;">
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Filtrar
                </button>
                
                <button type="button" class="btn btn-secondary" onclick="limpiarFiltros()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Limpiar
                </button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div class="card-title" style="margin-bottom: 0; padding-bottom: 0; border-bottom: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Resultados ({{ $citas->count() }} citas)
        </div>
        
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('reportes.excel', request()->all()) }}" class="btn btn-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Exportar Excel
            </a>
            
            {{-- BOTÓN PDF ACTUALIZADO --}}
            <a href="{{ route('reportes.pdf') }}?{{ http_build_query(request()->all()) }}" class="btn btn-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exportar PDF
            </a>
        </div>
    </div>
    
    @if($citas->count() > 0)
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Paciente</th>
                        <th>Documento</th>
                        <th>Especialidad</th>
                        <th>Médico</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado Cita</th>
                        <th>Diagnóstico</th>
                        <th>Estado Clínico</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($citas as $cita)
                        <tr>
                            <td>{{ $cita->id }}</td>
                            <td>{{ $cita->paciente->name ?? 'N/A' }}</td>
                            <td>{{ $cita->paciente->num_doc ?? 'N/A' }}</td>
                            <td>{{ $cita->especialidad->nombre ?? 'N/A' }}</td>
                            <td>{{ $cita->medico->name ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($cita->fecha_cita)->format('d/m/Y') }}</td>
                            <td>{{ $cita->hora_cita }}</td>
                            <td>
                                @if($cita->estado == 'Pendiente')
                                    <span class="badge badge-warning">Pendiente</span>
                                @elseif($cita->estado == 'Atendida')
                                    <span class="badge badge-success">Atendida</span>
                                @else
                                    <span class="badge badge-danger">Cancelada</span>
                                @endif
                            </td>
                            <td>
                                {{ $cita->paciente->historialClinico->diagnostico_principal ?? 'Sin historial' }}
                            </td>
                            <td>
                                @if($cita->paciente->historialClinico)
                                    <span class="badge
                                        {{ $cita->paciente->historialClinico->estado_paciente == 'Estable'        ? 'badge-success' : '' }}
                                        {{ $cita->paciente->historialClinico->estado_paciente == 'En Tratamiento' ? 'badge-warning' : '' }}
                                        {{ $cita->paciente->historialClinico->estado_paciente == 'Crítico'        ? 'badge-danger'  : '' }}">
                                        {{ $cita->paciente->historialClinico->estado_paciente }}
                                    </span>
                                @else
                                    <span class="badge badge-primary">Sin datos</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 60px 20px; color: var(--text-light);">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p style="margin-top: 16px; font-size: 16px;">No se encontraron citas con los filtros seleccionados.</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function limpiarFiltros() {
        const form = document.getElementById('filtrosForm');
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.name) {
                input.value = '';
            }
        });
        form.submit();
    }
</script>
@endpush

@endsection