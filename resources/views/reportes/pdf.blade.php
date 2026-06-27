<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 20px; }

        .membrete {
            text-align: center;
            border-bottom: 3px solid #003876;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .membrete h1 { color: #003876; font-size: 22px; margin: 0 0 4px; }
        .membrete p  { color: #475569; font-size: 12px; margin: 2px 0; }
        .membrete .fecha { font-size: 10px; color: #94a3b8; margin-top: 6px; }

        .titulo-reporte {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #003876;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }

        th {
            background: #003876;
            color: #fff;
            padding: 9px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
            vertical-align: top;
        }

        tr:nth-child(even) td { background: #f8fafc; }

        .estado-pendiente  { color: #92400e; font-weight: bold; }
        .estado-atendida   { color: #065f46; font-weight: bold; }
        .estado-cancelada  { color: #991b1b; font-weight: bold; }
        .estado-estable    { color: #065f46; font-weight: bold; }
        .estado-tratamiento{ color: #92400e; font-weight: bold; }
        .estado-critico    { color: #991b1b; font-weight: bold; }

        .total {
            text-align: right;
            font-size: 12px;
            font-weight: bold;
            color: #003876;
            margin-top: 12px;
            padding-right: 8px;
        }

        .footer {
            margin-top: 28px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }

        .sin-datos { color: #94a3b8; font-style: italic; }
    </style>
</head>
<body>

    {{-- MEMBRETE INSTITUCIONAL --}}
    <div class="membrete">
        <h1>POLICLINICO FLORES</h1>
        <p>Sistema de Gestión de Citas Médicas</p>
        <div class="fecha">Generado el: {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    <div class="titulo-reporte">Reporte de Citas Médicas</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
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
                <td>{{ $loop->iteration }}</td>
                <td>{{ $cita->paciente->name ?? '-' }}</td>
                <td>{{ ($cita->paciente->tipo_doc ?? '') }}: {{ ($cita->paciente->num_doc ?? '-') }}</td>
                <td>{{ $cita->especialidad->descripcion ?? '-' }}</td>
                <td>{{ $cita->medico->name ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($cita->fecha_cita)->format('d/m/Y') }}</td>
                <td>{{ $cita->hora_cita }}</td>
                <td class="estado-{{ strtolower($cita->estado) }}">{{ $cita->estado }}</td>
                <td>
                    @if($cita->paciente->historialClinico)
                        {{ $cita->paciente->historialClinico->diagnostico_principal ?? '-' }}
                    @else
                        <span class="sin-datos">Sin historial</span>
                    @endif
                </td>
                <td>
                    @if($cita->paciente->historialClinico)
                        @php $estado = $cita->paciente->historialClinico->estado_paciente; @endphp
                        <span class="
                            {{ $estado == 'Estable'        ? 'estado-estable'     : '' }}
                            {{ $estado == 'En Tratamiento' ? 'estado-tratamiento' : '' }}
                            {{ $estado == 'Crítico'        ? 'estado-critico'     : '' }}">
                            {{ $estado }}
                        </span>
                    @else
                        <span class="sin-datos">-</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">Total de citas: {{ $citas->count() }}</div>

    <div class="footer">
        Policlínico Flores — Reporte generado automáticamente por el Sistema de Gestión Médica
    </div>

</body>
</html>