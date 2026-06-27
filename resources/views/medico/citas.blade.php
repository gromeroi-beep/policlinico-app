@extends('layouts.medico')

@section('content')
<div class="page-header">
    <h1>📋 Mis Citas</h1>
    <p>Citas médicas asignadas a su consulta</p>
</div>

<div class="card">
    <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
        <span>📋 Listado de Citas</span>
        <span class="badge badge-primary" style="font-size:13px;">
            Total: {{ $citas->count() }}
        </span>
    </div>

    @if($citas->isEmpty())
        <div class="alert alert-warning">No tiene citas asignadas aún.</div>
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
                <th>Cambiar Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($citas as $cita)
            <tr>
                <td><strong>{{ $loop->iteration }}</strong></td>
                {{-- MITIGACIÓN XSS: {{ }} escapa automáticamente --}}
                <td>{{ $cita->paciente->name ?? '-' }}</td>
                <td>
                    <span class="badge badge-primary">{{ $cita->paciente->tipo_doc ?? '' }}</span>
                    {{ $cita->paciente->num_doc ?? '-' }}
                </td>
                <td>{{ $cita->especialidad->descripcion ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($cita->fecha_cita)->format('d/m/Y') }}</td>
                <td>{{ $cita->hora_cita }}</td>
                <td>
                    <span class="badge
                        {{ $cita->estado == 'Pendiente'  ? 'badge-warning' : '' }}
                        {{ $cita->estado == 'Atendida'   ? 'badge-success' : '' }}
                        {{ $cita->estado == 'Cancelada'  ? 'badge-danger'  : '' }}">
                        {{ $cita->estado }}
                    </span>
                </td>
                <td>
                    {{-- MITIGACIÓN CSRF: @csrf en formulario de actualización --}}
                    <form method="POST" action="{{ route('medico.citas.update', $cita->id) }}" style="margin:0;">
                        @csrf
                        @method('PUT')
                        <select name="estado" class="form-control"
                            style="padding:4px 8px; font-size:12px; width:130px;"
                            onchange="this.form.submit()">
                            <option value="Pendiente"  {{ $cita->estado == 'Pendiente'  ? 'selected' : '' }}>Pendiente</option>
                            <option value="Atendida"   {{ $cita->estado == 'Atendida'   ? 'selected' : '' }}>Atendida</option>
                            <option value="Cancelada"  {{ $cita->estado == 'Cancelada'  ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection