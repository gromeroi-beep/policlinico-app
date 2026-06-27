<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Policlínico Flores — Sistema d...</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ═══════════════════════════════════════
           VARIABLES GLOBALES — Mismo estilo original
        ═══════════════════════════════════════ */
        :root {
            --primary:       #003876;
            --primary-light: #0055a5;
            --primary-dark:  #002249;
            --accent:        #00a86b;
            --accent-light:  #10b981;
            --warning:       #f59e0b;
            --danger:        #ef4444;
            --text-dark:     #1e293b;
            --text-mid:      #475569;
            --text-light:    #64748b;
            --bg:            #f1f5f9;
            --white:         #ffffff;
            --border:        #e2e8f0;
            --shadow-sm:     0 1px 3px 0 rgba(0,56,118,0.04), 0 1px 2px -1px rgba(0,56,118,0.04);
            --shadow-md:     0 4px 6px -1px rgba(0,56,118,0.06), 0 2px 4px -2px rgba(0,56,118,0.06);
            --shadow-lg:     0 10px 15px -3px rgba(0,56,118,0.06), 0 4px 6px -4px rgba(0,56,118,0.04);
            --radius-sm:     8px;
            --radius-md:     12px;
            --radius-lg:     16px;
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-font-smoothing:antialiased; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            display: flex;
            min-height: 100vh;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* ═══════════════════════════════════════
           BANNER MODO SEGURIDAD — Top del sistema
        ═══════════════════════════════════════ */
        .security-banner {
            position: fixed;
            top: 0; left: 268px; right: 0;
            z-index: 200;
            padding: 7px 24px;
            font-size: 12px; font-weight: 600;
            display: flex; align-items: center; gap: 8px;
            font-family: 'Inter', sans-serif;
        }
        .security-banner.seguro {
            background: #065f46;
            color: #d1fae5;
        }
        .security-banner.inseguro {
            background: #991b1b;
            color: #fee2e2;
            animation: pulse-banner 2s infinite;
        }
        @keyframes pulse-banner {
            0%, 100% { opacity: 1; }
            50%       { opacity: .88; }
        }
        .banner-dot {
            width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
        }

        /* ═══════════════════════════════════════
           SIDEBAR — Estilo original del proyecto
        ═══════════════════════════════════════ */
        .sidebar {
            width: 268px;
            height: 100vh;
            background: var(--primary);
            display: flex;
            flex-direction: column;
            position: fixed;
            left: 0; top: 0;
            z-index: 100;
            border-right: 1px solid rgba(255,255,255,0.04);
        }

        .sidebar-logo {
            padding: 32px 24px;
            background: var(--primary-dark);
        }

        .sidebar-logo .logo-wrap {
            display: flex; align-items: center; gap: 12px;
        }

        .logo-icon-box {
            width: 42px; height: 42px;
            background: var(--white);
            border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            box-shadow: var(--shadow-md);
            flex-shrink: 0;
        }

        .logo-icon-box svg { width: 24px; height: 24px; color: var(--primary); }

        .logo-text h2 {
            color: var(--white); font-size: 16px; font-weight: 700;
            line-height: 1.2; letter-spacing: -0.3px;
        }

        .logo-text span {
            color: rgba(255,255,255,0.55); font-size: 11px;
            font-weight: 500; display: block; margin-top: 1px;
        }

        .sidebar-menu {
            flex: 1; padding: 24px 14px; overflow-y: auto;
        }

        .sidebar-menu::-webkit-scrollbar { width: 4px; }
        .sidebar-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }

        .menu-section-label {
            padding: 12px 12px 6px;
            font-size: 11px;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 700;
        }

        .sidebar-menu a {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 14px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px; font-weight: 500;
            border-radius: var(--radius-sm);
            margin-bottom: 4px;
            transition: all 0.2s ease-in-out;
        }

        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.06);
            color: var(--white);
        }

        .sidebar-menu a.active {
            background: var(--primary-light);
            color: var(--white); font-weight: 600;
            box-shadow: inset 0 1px 1px rgba(255,255,255,0.15);
        }

        .menu-icon {
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; transition: transform 0.2s ease;
        }

        .sidebar-menu a:hover .menu-icon { transform: translateX(1px); }

        /* Badge de seguridad en el menú */
        .sec-badge {
            margin-left: auto;
            font-size: 10px; font-weight: 700;
            padding: 2px 7px; border-radius: 20px;
            letter-spacing: 0.3px;
        }
        .sec-badge.on  { background: rgba(0,168,107,0.25); color: #6ee7b7; }
        .sec-badge.off { background: rgba(239,68,68,0.25);  color: #fca5a5; }

        .sidebar-footer {
            padding: 20px 16px;
            background: var(--primary-dark);
            border-top: 1px solid rgba(255,255,255,0.04);
        }

        .user-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: var(--radius-md);
            padding: 12px; margin-bottom: 12px;
            display: flex; align-items: center; gap: 12px;
        }

        .user-avatar {
            width: 38px; height: 38px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: var(--white); font-size: 15px; font-weight: 700;
            flex-shrink: 0;
        }

        .user-info-text strong {
            color: var(--white); font-size: 13px;
            font-weight: 600; display: block;
        }

        .user-info-text span {
            color: rgba(255,255,255,0.5);
            font-size: 11px; display: block; margin-top: 1px;
        }

        .btn-logout {
            width: 100%; padding: 10px 14px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: var(--radius-sm);
            color: rgba(255,255,255,0.7);
            font-size: 13px; font-weight: 500;
            cursor: pointer; display: flex;
            align-items: center; justify-content: center; gap: 8px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background: rgba(255,255,255,0.1);
            color: var(--white);
        }

        /* ═══════════════════════════════════════
           CONTENIDO PRINCIPAL
        ═══════════════════════════════════════ */
        .main-content {
            margin-left: 268px;
            flex: 1;
            padding: 36px;
            padding-top: 56px; /* espacio para el banner de seguridad */
            min-height: 100vh;
        }

        /* ═══════════════════════════════════════
           PAGE HEADER
        ═══════════════════════════════════════ */
        .page-header { margin-bottom: 32px; }

        .page-header h1 {
            font-size: 26px; font-weight: 800;
            color: var(--primary); letter-spacing: -0.5px;
        }

        .page-header p {
            color: var(--text-light); font-size: 14px; margin-top: 4px;
        }

        /* ═══════════════════════════════════════
           CARDS
        ═══════════════════════════════════════ */
        .card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 28px;
            box-shadow: var(--shadow-md);
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 15px; font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 8px;
        }

        /* ═══════════════════════════════════════
           FORMS
        ═══════════════════════════════════════ */
        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block; font-size: 11px; font-weight: 700;
            color: var(--text-mid); margin-bottom: 6px;
            text-transform: uppercase; letter-spacing: 0.8px;
        }

        .form-control {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 14px; color: var(--text-dark);
            background: #f8fafc;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary-light);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(0,85,165,0.08);
        }

        select.form-control { cursor: pointer; }

        /* ═══════════════════════════════════════
           BUTTONS
        ═══════════════════════════════════════ */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            font-size: 13px; font-weight: 600;
            cursor: pointer; border: none;
            text-decoration: none;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
        }

        .btn svg { width: 15px; height: 15px; }

        .btn-primary { background: var(--primary); color: var(--white); box-shadow: var(--shadow-sm); }
        .btn-primary:hover { background: var(--primary-light); }

        .btn-success { background: var(--accent); color: var(--white); box-shadow: var(--shadow-sm); }
        .btn-success:hover { background: var(--accent-light); }

        .btn-danger { background: var(--danger); color: var(--white); box-shadow: var(--shadow-sm); }
        .btn-danger:hover { background: #dc2626; }

        .btn-secondary { background: var(--bg); color: var(--text-mid); border: 1.5px solid var(--border); }
        .btn-secondary:hover { background: var(--border); color: var(--text-dark); }

        .btn-sm { padding: 6px 12px; font-size: 12px; }

        /* ═══════════════════════════════════════
           TABLES
        ═══════════════════════════════════════ */
        .table { width: 100%; border-collapse: collapse; }

        .table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid var(--border);
        }

        .table th {
            padding: 16px 20px; font-size: 11px; font-weight: 700;
            color: var(--text-mid); text-transform: uppercase; letter-spacing: 0.8px;
        }

        .table td {
            padding: 16px 20px; font-size: 14px;
            color: var(--text-dark);
            border-bottom: 1px solid #f1f5f9;
            background: var(--white);
        }

        .table tbody tr:hover td { background: #f8faff; }
        .table tbody tr:last-child td { border-bottom: none; }

        /* ═══════════════════════════════════════
           ALERTS
        ═══════════════════════════════════════ */
        .alert {
            padding: 14px 18px; border-radius: var(--radius-md);
            margin-bottom: 20px; font-size: 14px; font-weight: 500;
            display: flex; align-items: center; gap: 10px;
            box-shadow: var(--shadow-sm);
        }
        .alert svg { width: 18px; height: 18px; flex-shrink: 0; }
        .alert-success { background: #f0fdf4; color: #166534; border-left: 4px solid var(--accent); }
        .alert-danger  { background: #fef2f2; color: #991b1b; border-left: 4px solid var(--danger); }
        .alert-warning { background: #fffbeb; color: #92400e; border-left: 4px solid var(--warning); }
        .alert-info    { background: #eff6ff; color: #1e40af; border-left: 4px solid var(--primary); }

        /* ═══════════════════════════════════════
           BADGES
        ═══════════════════════════════════════ */
        .badge { padding: 5px 11px; border-radius: 50px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-primary { background: #dbeafe; color: #1e40af; }
    </style>
    @stack('styles')
</head>
<body>

@php
    try {
        $modoSeguroGlobal = (bool) \Illuminate\Support\Facades\DB::table('security_settings')
            ->where('clave', 'modo_seguro')->value('valor');
    } catch (\Exception $e) {
        $modoSeguroGlobal = true;
    }
@endphp

{{-- ═══════════════════════════════════════════════════════
     BANNER DE MODO SEGURIDAD
     Visible en la parte superior de todas las páginas del sistema.
     ROJO parpadeante = modo inseguro (OWASP ZAP detectará todo)
     VERDE = modo seguro (controles NIST activos)
═══════════════════════════════════════════════════════ --}}
<div class="security-banner {{ $modoSeguroGlobal ? 'seguro' : 'inseguro' }}">
    <div class="banner-dot" style="background: {{ $modoSeguroGlobal ? '#6ee7b7' : '#fca5a5' }}"></div>
    @if($modoSeguroGlobal)
        🟢 MODO SEGURO — Controles NIST SP 800-53 ACTIVOS — OWASP ZAP: Sin vulnerabilidades detectables
    @else
        🔴 ⚠ MODO INSEGURO — Sistema EXPUESTO — OWASP ZAP detectará las 10 vulnerabilidades — Controles NIST DESACTIVADOS
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════
     SIDEBAR — Menú diferenciado por rol (admin / médico)
═══════════════════════════════════════════════════════ --}}
<div class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-wrap">
            <div class="logo-icon-box">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6m-5.437-3.75a11.95 11.95 0 0115.874 0M12 3v1.5m0 15V21m-7.5-7.5h1.5m13.5 0h1.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="logo-text">
                <h2>Policlínico Flores</h2>
                <span>{{ auth()->user()->role === 'admin' ? 'Gestión Hospitalaria' : 'Portal Médico' }}</span>
            </div>
        </div>
    </div>

    <nav class="sidebar-menu">

        {{-- PRINCIPAL --}}
        <div class="menu-section-label">Principal</div>

        @if(auth()->user()->role === 'admin')
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/>
                </svg>
            </div>
            Dashboard
        </a>
        @else
        <a href="{{ route('medico.dashboard') }}" class="{{ request()->routeIs('medico.dashboard') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/>
                </svg>
            </div>
            Dashboard
        </a>
        @endif

        {{-- ADMINISTRACIÓN — Solo admin --}}
        @if(auth()->user()->role === 'admin')
        <div class="menu-section-label">Administración</div>

        <a href="{{ route('especialidades.index') }}" class="{{ request()->routeIs('especialidades.*') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
            Especialidades
        </a>

        <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            Usuarios
        </a>

        <a href="{{ route('historiales.index') }}" class="{{ request()->routeIs('historiales.*') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            Historial Clínico
        </a>
        @endif

        {{-- OPERACIONES --}}
        <div class="menu-section-label">Operaciones</div>

        @if(auth()->user()->role === 'admin')
        <a href="{{ route('programaciones.index') }}" class="{{ request()->routeIs('programaciones.*') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            Programación Médica
        </a>

        <a href="{{ route('citas.index') }}" class="{{ request()->routeIs('citas.*') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            Registro de Citas
        </a>
        @else
        <a href="{{ route('medico.citas') }}" class="{{ request()->routeIs('medico.citas*') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            Mis Citas
        </a>

        <a href="{{ route('medico.historiales') }}" class="{{ request()->routeIs('medico.historiales*') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            Historial Clínico
        </a>
        @endif

        {{-- REPORTES --}}
        <div class="menu-section-label">Reportes</div>

        @if(auth()->user()->role === 'admin')
        <a href="{{ route('reportes.index') }}" class="{{ request()->routeIs('reportes.*') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            Reportes y Exportación
        </a>
        @else
        <a href="{{ route('medico.reportes') }}" class="{{ request()->routeIs('medico.reportes*') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            Mis Reportes
        </a>
        @endif

        {{-- SEGURIDAD — Solo admin, reemplaza Demo Seguridad --}}
        @if(auth()->user()->role === 'admin')
        <div class="menu-section-label">Seguridad</div>

        <a href="{{ route('seguridad.index') }}" class="{{ request()->routeIs('seguridad.*') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            Panel OWASP / NIST
            <span class="sec-badge {{ $modoSeguroGlobal ? 'on' : 'off' }}">
                {{ $modoSeguroGlobal ? '✓ ON' : '✗ OFF' }}
            </span>
        </a>
        @endif

    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="user-info-text">
                <strong>{{ auth()->user()->name }}</strong>
                <span>{{ auth()->user()->role === 'admin' ? 'Administrador' : 'Médico' }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Cerrar Sesión
            </button>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     CONTENIDO PRINCIPAL
═══════════════════════════════════════════════════════ --}}
<div class="main-content">

    @if(session('success'))
        <div class="alert alert-success">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('toggle_resultado'))
        <div class="alert {{ str_contains(session('toggle_resultado'), '🟢') ? 'alert-success' : 'alert-danger' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('toggle_resultado') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    @yield('content')
</div>

@stack('scripts')
</body>
</html>