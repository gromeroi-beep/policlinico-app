<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Policlínico Flores — Portal Médico</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ═══════════════════════════════════════
           VARIABLES GLOBALES
        ═══════════════════════════════════════ */
        :root {
            --primary:       #003876;
            --primary-light: #0055a5;
            --primary-dark:  #002249;
            --accent:        #00a86b;
            --accent-light:  #10b981;
            --medico-accent: #0ea5e9;
            --warning:       #f59e0b;
            --danger:        #ef4444;

            --text-dark:     #1e293b;
            --text-mid:      #475569;
            --text-light:    #64748b;
            --bg:            #f1f5f9;
            --white:         #ffffff;
            --border:        #e2e8f0;

            --shadow-sm:     0 1px 3px 0 rgba(0, 56, 118, 0.04), 0 1px 2px -1px rgba(0, 56, 118, 0.04);
            --shadow-md:     0 4px 6px -1px rgba(0, 56, 118, 0.06), 0 2px 4px -2px rgba(0, 56, 118, 0.06);
            --shadow-lg:     0 10px 15px -3px rgba(0, 56, 118, 0.06), 0 4px 6px -4px rgba(0, 56, 118, 0.04);

            --radius-sm:     8px;
            --radius-md:     12px;
            --radius-lg:     16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            display: flex;
            min-height: 100vh;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* ═══════════════════════════════════════
           SIDEBAR MÉDICO
           Idéntico al admin pero con acento verde
           y solo las secciones permitidas
        ═══════════════════════════════════════ */
        .sidebar {
            width: 268px;
            height: 100vh;
            background: var(--primary);
            display: flex;
            flex-direction: column;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            border-right: 1px solid rgba(255, 255, 255, 0.04);
        }

        .sidebar-logo {
            padding: 32px 24px;
            background: var(--primary-dark);
        }

        .sidebar-logo .logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon-box {
            width: 42px;
            height: 42px;
            background: var(--white);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
            flex-shrink: 0;
        }

        .logo-icon-box svg {
            width: 24px;
            height: 24px;
            color: var(--primary);
        }

        .logo-text h2 {
            color: var(--white);
            font-size: 16px;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.3px;
        }

        .logo-text span {
            color: rgba(255, 255, 255, 0.55);
            font-size: 11px;
            font-weight: 500;
            display: block;
            margin-top: 1px;
        }

        /* Badge de rol médico */
        .rol-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 8px;
            background: rgba(0, 168, 107, 0.2);
            border: 1px solid rgba(0, 168, 107, 0.3);
            color: #6ee7b7;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .sidebar-menu {
            flex: 1;
            padding: 24px 14px;
            overflow-y: auto;
        }

        .sidebar-menu::-webkit-scrollbar { width: 4px; }
        .sidebar-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }

        .menu-section-label {
            padding: 12px 12px 6px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 700;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: var(--radius-sm);
            margin-bottom: 4px;
            transition: all 0.2s ease-in-out;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.06);
            color: var(--white);
        }

        .sidebar-menu a.active {
            background: var(--primary-light);
            color: var(--white);
            font-weight: 600;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.15);
        }

        .sidebar-menu a .menu-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .sidebar-menu a:hover .menu-icon {
            transform: translateX(1px);
        }

        .sidebar-menu a.active .menu-icon svg {
            color: var(--accent);
        }

        .sidebar-footer {
            padding: 20px 16px;
            background: var(--primary-dark);
            border-top: 1px solid rgba(255, 255, 255, 0.04);
        }

        .user-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-md);
            padding: 12px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            font-size: 15px;
            flex-shrink: 0;
        }

        .user-info-text strong {
            display: block;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
        }

        .user-info-text span {
            font-size: 11px;
            color: rgba(255,255,255,0.45);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-logout {
            width: 100%;
            padding: 10px 14px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #fff;
        }

        /* ═══════════════════════════════════════
           MAIN CONTENT
        ═══════════════════════════════════════ */
        .main-content {
            margin-left: 268px;
            flex: 1;
            padding: 36px 40px;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-header p {
            color: var(--text-light);
            font-size: 14px;
            margin-top: 6px;
            font-weight: 400;
        }

        /* ═══════════════════════════════════════
           CARDS
        ═══════════════════════════════════════ */
        .card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 28px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 24px;
            border: 1px solid var(--border);
        }

        .card-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ═══════════════════════════════════════
           FORMS
        ═══════════════════════════════════════ */
        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-mid);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .form-control {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 14px;
            color: var(--text-dark);
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            background: #fff;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(0, 85, 165, 0.08);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        /* ═══════════════════════════════════════
           BUTTONS
        ═══════════════════════════════════════ */
        .btn {
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            border: none;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 600;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.1px;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(0, 56, 118, 0.2);
        }
        .btn-primary:hover {
            background: var(--primary-light);
            box-shadow: 0 4px 12px rgba(0, 56, 118, 0.3);
            transform: translateY(-1px);
        }

        .btn-success {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 2px 8px rgba(0, 168, 107, 0.2);
        }
        .btn-success:hover { background: var(--accent-light); transform: translateY(-1px); }

        .btn-warning {
            background: var(--warning);
            color: #fff;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
        }
        .btn-warning:hover { opacity: 0.9; transform: translateY(-1px); }

        .btn-danger {
            background: var(--danger);
            color: #fff;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
        }
        .btn-danger:hover { opacity: 0.9; transform: translateY(-1px); }

        .btn-secondary {
            background: var(--bg);
            color: var(--text-mid);
            border: 1.5px solid var(--border);
        }
        .btn-secondary:hover { background: var(--border); color: var(--text-dark); }

        /* ═══════════════════════════════════════
           TABLES
        ═══════════════════════════════════════ */
        .table { width: 100%; border-collapse: collapse; }

        .table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid var(--border);
        }

        .table th {
            padding: 16px 20px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-mid);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .table td {
            padding: 16px 20px;
            font-size: 14px;
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
            padding: 16px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-sm);
        }
        .alert-success { background: #f0fdf4; color: #166534; border-left: 4px solid var(--accent); }
        .alert-danger  { background: #fef2f2; color: #991b1b; border-left: 4px solid var(--danger); }
        .alert-warning { background: #fffbeb; color: #92400e; border-left: 4px solid var(--warning); }

        /* ═══════════════════════════════════════
           BADGES
        ═══════════════════════════════════════ */
        .badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-primary { background: #dbeafe; color: #1e40af; }
    </style>
    @stack('styles')
</head>
<body>

{{-- ═══════════════════════════════════════
     SIDEBAR MÉDICO — Acceso Restringido
     RBAC: Solo muestra opciones del médico
═══════════════════════════════════════ --}}
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
                <span>Portal Médico</span>
                <div class="rol-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Acceso Médico
                </div>
            </div>
        </div>
    </div>

    <nav class="sidebar-menu">

        {{-- PRINCIPAL --}}
        <div class="menu-section-label">Principal</div>
        <a href="{{ route('medico.dashboard') }}"
           class="{{ request()->routeIs('medico.dashboard') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/>
                </svg>
            </div>
            Dashboard
        </a>

        {{-- MIS OPERACIONES --}}
        <div class="menu-section-label">Mis Operaciones</div>
        <a href="{{ route('medico.citas') }}"
           class="{{ request()->routeIs('medico.citas*') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            Mis Citas
        </a>

        <a href="{{ route('medico.historiales') }}"
           class="{{ request()->routeIs('medico.historiales*') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            Historial Clínico
        </a>

        {{-- REPORTES --}}
        <div class="menu-section-label">Reportes</div>
        <a href="{{ route('medico.reportes') }}"
           class="{{ request()->routeIs('medico.reportes*') ? 'active' : '' }}">
            <div class="menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            Mis Reportes
        </a>

    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">
                {{ strtoupper(substr(Auth::user()->name ?? Auth::user()->username, 0, 1)) }}
            </div>
            <div class="user-info-text">
                {{-- MITIGACIÓN XSS: {{ }} escapa automáticamente --}}
                <strong>{{ Auth::user()->name }}</strong>
                <span>{{ Auth::user()->especialidad->descripcion ?? 'Médico' }}</span>
            </div>
        </div>

        {{-- MITIGACIÓN CSRF: @csrf en el form de logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Cerrar Sesión
            </button>
        </form>
    </div>
</div>

{{-- CONTENIDO PRINCIPAL --}}
<div class="main-content">

    @if(session('success'))
        <div class="alert alert-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @yield('content')
</div>

@stack('scripts')
</body>
</html>