@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>📊 Dashboard</h1>
    <p>Bienvenido al Sistema de Gestión del Policlínico Flores</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 32px;">
    
    <div class="card" style="text-align: center; border-top: 4px solid #4fc3f7; transition: transform 0.2s ease-in-out; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px 24px;">
        <div style="font-size: 40px; margin-bottom: 12px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.05));">🩺</div>
        <div style="font-size: 32px; font-weight: 800; color: var(--primary); margin-bottom: 4px; letter-spacing: -0.5px;">
            {{ \App\Models\Especialidad::count() }}
        </div>
        <div style="color: var(--text-light); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Especialidades</div>
    </div>

    <div class="card" style="text-align: center; border-top: 4px solid var(--accent); transition: transform 0.2s ease-in-out; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px 24px;">
        <div style="font-size: 40px; margin-bottom: 12px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.05));">👨‍⚕️</div>
        <div style="font-size: 32px; font-weight: 800; color: var(--primary); margin-bottom: 4px; letter-spacing: -0.5px;">
            {{ \App\Models\User::where('role','medico')->count() }}
        </div>
        <div style="color: var(--text-light); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Médicos</div>
    </div>

    <div class="card" style="text-align: center; border-top: 4px solid var(--warning); transition: transform 0.2s ease-in-out; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px 24px;">
        <div style="font-size: 40px; margin-bottom: 12px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.05));">🧑‍🤝‍🧑</div>
        <div style="font-size: 32px; font-weight: 800; color: var(--primary); margin-bottom: 4px; letter-spacing: -0.5px;">
            {{ \App\Models\User::where('role','paciente')->count() }}
        </div>
        <div style="color: var(--text-light); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Pacientes</div>
    </div>

    <div class="card" style="text-align: center; border-top: 4px solid var(--danger); transition: transform 0.2s ease-in-out; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px 24px;">
        <div style="font-size: 40px; margin-bottom: 12px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.05));">📋</div>
        <div style="font-size: 32px; font-weight: 800; color: var(--primary); margin-bottom: 4px; letter-spacing: -0.5px;">
            {{ \App\Models\Cita::where('estado','Pendiente')->count() }}
        </div>
        <div style="color: var(--text-light); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Citas Pendientes</div>
    </div>

</div>
@endsection