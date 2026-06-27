@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>👥 Gestión de Usuarios</h1>
    <p>Registre médicos y pacientes del policlínico</p>
</div>

{{-- ══════════════════════════════════════════════════════════════
     VULNERABILIDAD #8 — Sensitive Data Exposure
     VULNERABILIDAD #5 — Mass Assignment
     Banner que indica al profesor qué está ocurriendo en cada modo
     ══════════════════════════════════════════════════════════════ --}}
@if(!$modoSeguro)
<div style="
    background: #ffebee; border: 1.5px solid #ef9a9a;
    border-left: 5px solid #c62828;
    border-radius: 8px; padding: 14px 18px; margin-bottom: 20px;
">
    <div style="font-weight: 700; color: #b71c1c; font-size: 14px; margin-bottom: 6px;">
        🔴 MODO INSEGURO ACTIVO — Vulnerabilidades #5 y #8 expuestas
    </div>
    <div style="font-size: 12px; color: #c62828; line-height: 1.6;">
        <strong>#5 Mass Assignment (NIST SI-10):</strong>
        El formulario usa <code style="background:#ffcdd2;padding:1px 5px;border-radius:3px;">$request->all()</code>
        — enviar <code style="background:#ffcdd2;padding:1px 5px;border-radius:3px;">role=admin</code>
        vía Postman/Burp Suite escala privilegios al instante.<br>
        <strong>#8 Sensitive Data Exposure (NIST SC-28 + IA-5):</strong>
        Las contraseñas están almacenadas en texto plano y son visibles en la tabla.
        OWASP ZAP detectará: <em>Information Disclosure – Sensitive Information</em>.
    </div>
</div>
@else
<div style="
    background: #e8f5e9; border: 1.5px solid #a5d6a7;
    border-left: 5px solid #2e7d32;
    border-radius: 8px; padding: 14px 18px; margin-bottom: 20px;
">
    <div style="font-weight: 700; color: #1b5e20; font-size: 14px; margin-bottom: 4px;">
        🟢 MODO SEGURO — Controles NIST SI-10 + SC-28 + IA-5 Activos
    </div>
    <div style="font-size: 12px; color: #2e7d32;">
        Mass Assignment protegido con <code style="background:#c8e6c9;padding:1px 5px;border-radius:3px;">$request->only([...])</code>
        · Contraseñas hasheadas con bcrypt · Campos sensibles ocultos.
    </div>
</div>
@endif

<div style="display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;">

    {{-- ══════════════════════════════════════════════════════════
         FORMULARIO DE REGISTRO
    ══════════════════════════════════════════════════════════════ --}}
    <div class="card">
        <div style="font-weight: 700; font-size: 15px; margin-bottom: 18px; color: #1a237e;">
            ➕ Nuevo Usuario
        </div>

        <form method="POST" action="{{ route('usuarios.store') }}">
            @csrf

            <div style="margin-bottom: 14px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#607d8b;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px;">
                    Tipo de Usuario
                </label>
                <select name="role" id="role" class="form-control" onchange="mostrarCampos()" required
                    style="width:100%;padding:10px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;">
                    <option value="">-- Seleccione --</option>
                    <option value="medico"   {{ old('role')=='medico'   ? 'selected' : '' }}>Médico</option>
                    <option value="paciente" {{ old('role')=='paciente' ? 'selected' : '' }}>Paciente</option>
                </select>
                @error('role')
                    <small style="color:#c62828;font-size:11px;margin-top:3px;display:block;">{{ $message }}</small>
                @enderror
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#607d8b;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px;">
                    Nombre Completo
                </label>
                <input type="text" name="name" class="form-control"
                    style="width:100%;padding:10px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;"
                    value="{{ old('name') }}" placeholder="Nombre completo" required>
                @error('name')
                    <small style="color:#c62828;font-size:11px;margin-top:3px;display:block;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Campos Médico --}}
            <div id="campos-medico" style="display:none;background:#f8fafc;padding:16px;border-radius:8px;border:1px dashed #e0e0e0;margin-bottom:14px;">

                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#607d8b;margin-bottom:5px;">Colegiatura</label>
                    <input type="text" name="colegiatura" class="form-control"
                        style="width:100%;padding:10px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;"
                        value="{{ old('colegiatura') }}" placeholder="Nº Colegiatura (5 dígitos)"
                        maxlength="5" inputmode="numeric"
                        oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,5)">
                    @error('colegiatura')
                        <small style="color:#c62828;font-size:11px;margin-top:3px;display:block;">{{ $message }}</small>
                    @enderror
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#607d8b;margin-bottom:5px;">Especialidad</label>
                    <select name="especialidad_id" class="form-control"
                        style="width:100%;padding:10px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;">
                        <option value="">-- Seleccione --</option>
                        @foreach($especialidades as $esp)
                            <option value="{{ $esp->id }}" {{ old('especialidad_id')==$esp->id ? 'selected' : '' }}>
                                {{ $esp->descripcion }}
                            </option>
                        @endforeach
                    </select>
                    @error('especialidad_id')
                        <small style="color:#c62828;font-size:11px;margin-top:3px;display:block;">{{ $message }}</small>
                    @enderror
                </div>

                <div style="border-top:1px dashed #e0e0e0;margin:12px 0;"></div>
                <p style="font-size:11px;font-weight:700;color:#90a4ae;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">
                    🔐 Credenciales de Acceso
                </p>

                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#607d8b;margin-bottom:5px;">Usuario (username)</label>
                    <input type="text" name="username" class="form-control"
                        style="width:100%;padding:10px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;"
                        value="{{ old('username') }}" placeholder="Ej: dr.garcia" autocomplete="off">
                    @error('username')
                        <small style="color:#c62828;font-size:11px;margin-top:3px;display:block;">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#607d8b;margin-bottom:5px;">Contraseña</label>
                    <input type="password" name="password" class="form-control"
                        style="width:100%;padding:10px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;"
                        placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                    @error('password')
                        <small style="color:#c62828;font-size:11px;margin-top:3px;display:block;">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            {{-- Campos Paciente --}}
            <div id="campos-paciente" style="display:none;background:#f8fafc;padding:16px;border-radius:8px;border:1px dashed #e0e0e0;margin-bottom:14px;">
                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#607d8b;margin-bottom:5px;">Tipo de Documento</label>
                    <select name="tipo_doc" id="tipo_doc" class="form-control" onchange="validarDoc()"
                        style="width:100%;padding:10px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;">
                        <option value="">-- Seleccione --</option>
                        <option value="DNI" {{ old('tipo_doc')=='DNI' ? 'selected' : '' }}>DNI</option>
                        <option value="CE"  {{ old('tipo_doc')=='CE'  ? 'selected' : '' }}>CE</option>
                    </select>
                    @error('tipo_doc')
                        <small style="color:#c62828;font-size:11px;margin-top:3px;display:block;">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#607d8b;margin-bottom:5px;">Número de Documento</label>
                    <input type="text" name="num_doc" id="num_doc" class="form-control"
                        style="width:100%;padding:10px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;"
                        value="{{ old('num_doc') }}" placeholder="Nº Documento">
                    <small id="hint-doc" style="color:#90a4ae;font-size:11px;margin-top:3px;display:block;"></small>
                    @error('num_doc')
                        <small style="color:#c62828;font-size:11px;margin-top:3px;display:block;">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <button type="submit"
                style="width:100%;padding:12px;background:#0d47a1;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;margin-top:8px;">
                💾 Guardar Usuario
            </button>
        </form>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         TABLA DE USUARIOS
         VULNERABILIDAD #8: En modo inseguro muestra la columna Password
         en texto plano — evidencia real de Sensitive Data Exposure
    ══════════════════════════════════════════════════════════════ --}}
    <div class="card">
        <div style="font-weight:700;font-size:15px;margin-bottom:18px;color:#1a237e;">
            📋 Usuarios Registrados
            @if(!$modoSeguro)
                <span style="font-size:11px;background:#ffebee;color:#c62828;padding:2px 8px;border-radius:10px;margin-left:8px;font-weight:600;">
                    ⚠ Datos sensibles expuestos
                </span>
            @endif
        </div>

        @if($usuarios->isEmpty())
            <div style="text-align:center;padding:32px;color:#90a4ae;">
                No hay usuarios registrados aún.
            </div>
        @else
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Detalle</th>
                        {{-- Columna de password SOLO visible en modo inseguro --}}
                        @if(!$modoSeguro)
                        <th style="background:#ffebee;color:#b71c1c;">
                            🔓 Password (EXPUESTA)
                        </th>
                        @endif
                        <th style="text-align:center;width:120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $usuario)
                    <tr @if(!$modoSeguro) style="background: #fff8f8;" @endif>
                        <td style="font-weight:700;color:#90a4ae;">{{ $loop->iteration }}</td>
                        <td style="font-weight:600;">{{ $usuario->name }}</td>
                        <td>
                            <span style="
                                padding:3px 10px; border-radius:10px; font-size:12px; font-weight:700;
                                background: {{ $usuario->role=='medico' ? '#e8f5e9' : '#fff8e1' }};
                                color: {{ $usuario->role=='medico' ? '#1b5e20' : '#f57f17' }};
                            ">
                                {{ ucfirst($usuario->role) }}
                            </span>
                        </td>
                        <td style="font-size:13px;color:#607d8b;">
                            @if($usuario->role === 'medico')
                                🩺 {{ $usuario->especialidad->descripcion ?? '-' }}
                            @else
                                🪪 {{ $usuario->tipo_doc }}: {{ $usuario->num_doc }}
                            @endif
                        </td>

                        {{-- ══════════════════════════════════════════════════════
                             VULNERABILIDAD #8 — Sensitive Data Exposure REAL
                             NIST SC-28 + IA-5 DESACTIVADOS en modo inseguro:
                             La password se muestra tal como está en la BD.
                             En modo inseguro fue guardada en texto plano.
                             En modo seguro esta columna no existe en la tabla.
                             OWASP ZAP detectará: Information Disclosure
                        ═════════════════════════════════════════════════════════ --}}
                        @if(!$modoSeguro)
                        <td style="background:#fff3f3;">
                            <code style="
                                font-size:11px;
                                background:#ffebee; color:#c62828;
                                padding:3px 8px; border-radius:4px;
                                font-family:monospace;
                            ">
                                {{ $usuario->password ?? '(sin password)' }}
                            </code>
                        </td>
                        @endif

                        <td style="text-align:center;">
                            <form method="POST" action="{{ route('usuarios.destroy', $usuario->id) }}"
                                onsubmit="return confirm('¿Eliminar este usuario?')" style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    🗑 Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<script>
function mostrarCampos() {
    const role = document.getElementById('role').value;
    document.getElementById('campos-medico').style.display   = role==='medico'   ? 'block' : 'none';
    document.getElementById('campos-paciente').style.display = role==='paciente' ? 'block' : 'none';
}
function validarDoc() {
    const tipo = document.getElementById('tipo_doc').value;
    const input = document.getElementById('num_doc');
    const hint  = document.getElementById('hint-doc');
    if (tipo==='DNI') { input.maxLength=8; hint.textContent='DNI: exactamente 8 dígitos'; }
    else if (tipo==='CE') { input.maxLength=10; hint.textContent='CE: exactamente 10 dígitos'; }
}
window.onload = function() {
    if (document.getElementById('role').value) mostrarCampos();
};
</script>
@endsection