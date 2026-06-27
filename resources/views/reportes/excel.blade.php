<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Paciente</th>
            <th>Documento</th>
            <th>Especialidad</th>
            <th>Médico</th>
            <th>Fecha Cita</th>
            <th>Hora Cita</th>
            <th>Estado Cita</th>
            <th>Grupo Sanguíneo</th>
            <th>Alergias</th>
            <th>Diagnóstico Principal</th>
            <th>Antecedentes</th>
            <th>Estado Clínico</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($citas as $cita)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $cita->paciente->name ?? '-' }}</td>
            <td>{{ ($cita->paciente->tipo_doc ?? '') . ': ' . ($cita->paciente->num_doc ?? '-') }}</td>
            <td>{{ $cita->especialidad->descripcion ?? '-' }}</td>
            <td>{{ $cita->medico->name ?? '-' }}</td>
            <td>{{ \Carbon\Carbon::parse($cita->fecha_cita)->format('d/m/Y') }}</td>
            <td>{{ $cita->hora_cita }}</td>
            <td>{{ $cita->estado }}</td>
            {{-- Datos del Historial Clínico --}}
            @if($cita->paciente->historialClinico)
                <td>{{ $cita->paciente->historialClinico->grupo_sanguineo ?? '-' }}</td>
                <td>{{ $cita->paciente->historialClinico->alergias ?? '-' }}</td>
                <td>{{ $cita->paciente->historialClinico->diagnostico_principal ?? '-' }}</td>
                <td>{{ $cita->paciente->historialClinico->antecedentes_medicos ?? '-' }}</td>
                <td>{{ $cita->paciente->historialClinico->estado_paciente ?? '-' }}</td>
                <td>{{ $cita->paciente->historialClinico->observaciones ?? '-' }}</td>
            @else
                <td>-</td>
                <td>-</td>
                <td>Sin historial</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>