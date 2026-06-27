@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>🩺 Gestión de Especialidades</h1>
    <p>Registre y administre las especialidades médicas del policlínico</p>
</div>

<div style="display: grid; grid-template-columns: 350px 1fr; gap: 24px; align-items: start;">

    {{-- FORMULARIO DE CREACIÓN --}}
    <div class="card">
        <div class="card-title">
            <span>➕ Nueva Especialidad</span>
        </div>

        <form method="POST" action="{{ route('especialidades.store') }}">
            @csrf

            <div class="form-group">
                <label>Descripción</label>
                <input
                    type="text"
                    name="descripcion"
                    class="form-control"
                    value="{{ old('descripcion') }}"
                    placeholder="Ej: Cardiología"
                    required
                >
                @error('descripcion')
                    <small style="color: var(--danger); font-weight: 500; margin-top: 4px; display: block;">{{ $message }}</small>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content: center;">
                <span>💾 Guardar Especialidad</span>
            </button>
        </form>
    </div>

    {{-- TABLA DE REGISTROS --}}
    <div class="card">
        <div class="card-title" style="display:flex; justify-content:space-between; align-items:center; padding-bottom: 14px; border-bottom: 2px solid var(--bg);">
            <span>📋 Especialidades Registradas</span>
            <span class="badge badge-primary" style="font-size:13px; font-weight: 600;">
                Total: {{ $total }}
            </span>
        </div>

        @if($especialidades->isEmpty())
            <div class="alert alert-warning">
                <span>No hay especialidades registradas aún.</span>
            </div>
        @else
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">#</th>
                    <th>Descripción</th>
                    <th style="width: 180px;">Fecha Registro</th>
                    <th style="width: 220px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($especialidades as $especialidad)
                <tr>
                    <td><strong>{{ $loop->iteration }}</strong></td>
                    <td>
                        <span style="font-weight: 500; color: var(--text-dark);">{{ $especialidad->descripcion }}</span>
                    </td>
                    <td style="color: var(--text-mid);">{{ $especialidad->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div style="display:flex; gap:8px; justify-content: center; align-items: center;">
                            {{-- EDITAR: Usamos dataset HTML5 para evitar errores con comillas y tildes --}}
                            <button 
                                type="button"
                                class="btn btn-warning btn-editar" 
                                data-id="{{ $especialidad->id }}" 
                                data-descripcion="{{ $especialidad->descripcion }}"
                                style="padding:6px 14px; font-size:12px; font-weight: 600;">
                                ✏️ Editar
                            </button>

                            {{-- ELIMINAR --}}
                            <form method="POST" action="{{ route('especialidades.destroy', $especialidad->id) }}"
                                onsubmit="return confirm('¿Eliminar esta especialidad?')" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding:6px 14px; font-size:12px; font-weight: 600;">
                                    🗑️ Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- MODAL EDITAR --}}
<div id="modalEditar" style="display:none; position:fixed; inset:0; background:rgba(15, 23, 42, 0.6); z-index:999; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div class="card" style="width:420px; padding:32px; box-shadow: var(--shadow-lg); margin-bottom: 0;">
        <h3 style="color: var(--primary); font-size: 20px; font-weight: 800; margin-bottom: 20px; letter-spacing: -0.5px;">✏️ Editar Especialidad</h3>
        <form id="formEditar" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Descripción</label>
                <input type="text" name="descripcion" id="editDescripcion" class="form-control" required placeholder="Ej: Pediatría">
            </div>
            <div style="display:flex; gap:12px; margin-top:24px; justify-content: flex-end;">
                <button type="button" onclick="cerrarEditar()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary">💾 Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Escuchar los clicks en los botones de editar de forma segura
    document.querySelectorAll('.btn-editar').forEach(boton => {
        boton.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const descripcion = this.getAttribute('data-descripcion');
            
            document.getElementById('modalEditar').style.display = 'flex';
            document.getElementById('editDescripcion').value = descripcion;
            
            // Ajustamos la acción de destino del formulario dinámicamente
            document.getElementById('formEditar').action = '/especialidades/' + id;
        });
    });
});

function cerrarEditar() {
    document.getElementById('modalEditar').style.display = 'none';
}
</script>
@endsection