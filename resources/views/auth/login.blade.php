<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Policlínico Flores</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:       #003876;
            --primary-light: #0055a5;
            --primary-dark:  #001f42;
            --accent:        #00a86b;
            --accent-light:  #10b981;
            --danger:        #ef4444;
            --text-dark:     #1e293b;
            --text-mid:      #475569;
            --text-light:    #64748b;
            --bg-field:      #f8fafc;
            --white:         #ffffff;
            --border:        #e2e8f0;
            --shadow-btn:    0 4px 14px 0 rgba(0, 56, 118, 0.25);
            --shadow-right:  -8px 0 32px rgba(0, 31, 66, 0.05);
            --radius-md:     12px;
            --radius-sm:     8px;
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-font-smoothing: antialiased; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f1f5f9;
            overflow-x: hidden;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(145deg, var(--primary-dark) 0%, var(--primary) 45%, var(--accent) 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            top: -150px; right: -100px;
        }

        .login-left::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(0,168,107,0.15) 0%, rgba(0,168,107,0) 70%);
            border-radius: 50%;
            bottom: -100px; left: -100px;
        }

        .left-content { position: relative; z-index: 1; text-align: center; width: 100%; max-width: 420px; }

        .left-logo {
            width: 80px; height: 80px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 32px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .left-logo svg { width: 40px; height: 40px; color: var(--white); }

        .left-content h1 {
            color: var(--white);
            font-size: 32px; font-weight: 800;
            letter-spacing: -0.75px; margin-bottom: 12px;
        }

        .left-content p {
            color: rgba(255, 255, 255, 0.75);
            font-size: 15px; line-height: 1.6;
            margin-bottom: 48px; font-weight: 400;
        }

        .left-features {
            display: flex; flex-direction: column; gap: 16px;
            text-align: left;
            background: rgba(0, 31, 66, 0.2);
            padding: 24px; border-radius: var(--radius-md);
            border: 1px solid rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(4px);
        }

        .feature-item {
            display: flex; align-items: center; gap: 14px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px; font-weight: 500;
        }

        .feature-icon {
            width: 32px; height: 32px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            color: var(--white); flex-shrink: 0;
        }

        .feature-icon svg { width: 18px; height: 18px; }

        .login-right {
            width: 480px;
            display: flex; align-items: center; justify-content: center;
            padding: 40px;
            background: var(--white);
            box-shadow: var(--shadow-right);
            z-index: 10;
        }

        .login-form-wrap { width: 100%; max-width: 360px; }

        .form-header { margin-bottom: 28px; }

        .form-header h2 {
            font-size: 26px; font-weight: 800;
            color: var(--primary); letter-spacing: -0.5px;
        }

        .form-header p {
            color: var(--text-light); font-size: 14px;
            margin-top: 6px; font-weight: 400;
        }

        .form-group { margin-bottom: 22px; }

        .form-group label {
            display: block; font-size: 11px; font-weight: 700;
            color: var(--text-mid); margin-bottom: 8px;
            text-transform: uppercase; letter-spacing: 0.8px;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            display: flex; align-items: center; justify-content: center;
            pointer-events: none;
        }

        .input-icon svg { width: 18px; height: 18px; }

        .form-control {
            width: 100%;
            padding: 13px 16px 13px 44px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 14px; color: var(--text-dark);
            background: var(--bg-field);
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary-light);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(0, 85, 165, 0.08);
        }

        .btn-login {
            width: 100%; padding: 14px;
            background: var(--primary); color: var(--white);
            border: none; border-radius: var(--radius-sm);
            font-size: 15px; font-weight: 600;
            cursor: pointer; font-family: 'Inter', sans-serif;
            transition: all 0.2s ease-in-out;
            box-shadow: var(--shadow-btn);
            margin-top: 12px; letter-spacing: 0.2px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }

        .btn-login:hover {
            background: var(--primary-light);
            box-shadow: 0 6px 20px rgba(0, 56, 118, 0.35);
            transform: translateY(-1px);
        }

        .btn-login svg { width: 16px; height: 16px; transition: transform 0.2s ease; }
        .btn-login:hover svg { transform: translateX(2px); }

        .alert-danger {
            background: #fef2f2; color: #991b1b;
            border-left: 4px solid var(--danger);
            border-radius: var(--radius-sm);
            padding: 14px 16px; font-size: 13.5px; font-weight: 500;
            margin-bottom: 24px;
            display: flex; align-items: center; gap: 10px;
        }

        .alert-danger svg { width: 18px; height: 18px; color: var(--danger); flex-shrink: 0; }

        .footer-login {
            text-align: center; margin-top: 32px;
            font-size: 12px; color: var(--text-light);
            border-top: 1px solid var(--border); padding-top: 24px;
        }

        .nist-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f0fdf4; border: 1px solid #bbf7d0;
            color: #166534; padding: 6px 14px; border-radius: 50px;
            font-size: 11px; font-weight: 600; margin-top: 12px;
        }

        .nist-badge svg { width: 14px; height: 14px; }

        /* ── BANNER DE MODO SEGURIDAD ──────────────────────────── */
        .security-status {
            padding: 10px 14px; border-radius: 8px;
            font-size: 12px; font-weight: 600;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
            line-height: 1.4;
        }
        .security-status.seguro {
            background: #f0fdf4; color: #166534;
            border: 1px solid #bbf7d0;
        }
        .security-status.inseguro {
            background: #fef2f2; color: #991b1b;
            border: 1px solid #fecaca;
            animation: pulse-border 2s infinite;
        }
        @keyframes pulse-border {
            0%, 100% { border-color: #fecaca; }
            50%       { border-color: #f87171; }
        }
        .status-dot {
            width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
        }
        .hint-vuln {
            font-size: 11px; font-weight: 500; margin-top: 5px;
            padding: 4px 8px; border-radius: 4px; display: block;
        }

        @media (max-width: 900px) {
            .login-left { display: none; }
            .login-right { width: 100%; }
        }
    </style>
</head>
<body>

@php
    try {
        $modoSeguroLogin = (bool) \Illuminate\Support\Facades\DB::table('security_settings')
            ->where('clave', 'modo_seguro')->value('valor');
    } catch (\Exception $e) {
        $modoSeguroLogin = true;
    }
@endphp

{{-- ── LADO IZQUIERDO —————————————————————————————————————— --}}
<div class="login-left">
    <div class="left-content">
        <div class="left-logo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6m-5.437-3.75a11.95 11.95 0 0115.874 0M12 3v1.5m0 15V21m-7.5-7.5h1.5m13.5 0h1.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1>Policlínico Flores</h1>
        <p>Sistema integral de gestión de citas médicas y atención al paciente</p>

        <div class="left-features">
            <div class="feature-item">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z" />
                    </svg>
                </div>
                <span>Gestión de citas y programaciones</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <span>Administración de médicos y pacientes</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <span>Reportes y exportación de datos</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <span>Seguridad NIST SP 800-53 implementada</span>
            </div>
        </div>
    </div>
</div>

{{-- ── LADO DERECHO — Formulario ——————————————————————————— --}}
<div class="login-right">
    <div class="login-form-wrap">

        <div class="form-header">
            <h2>Bienvenido</h2>
            <p>Ingrese sus credenciales para acceder al sistema</p>
        </div>

        {{-- ══════════════════════════════════════════════════════
             ESTADO DE SEGURIDAD EN TIEMPO REAL
             Lee security_settings desde la BD y muestra el modo actual.
             MODO INSEGURO → SQLi activo, sin rate limiting, CSRF off
             MODO SEGURO   → controles NIST SP 800-53 activos
        ═════════════════════════════════════════════════════════ --}}
        <div class="security-status {{ $modoSeguroLogin ? 'seguro' : 'inseguro' }}">
            <div class="status-dot" style="background: {{ $modoSeguroLogin ? '#16a34a' : '#dc2626' }}"></div>
            <div>
                @if($modoSeguroLogin)
                    🟢 <strong>Modo Seguro</strong> — NIST SP 800-53 Activo
                    <span style="font-weight:400;color:#15803d;"> | SQLi: Protegido | Brute Force: Limitado | CSRF: Verificado</span>
                @else
                    🔴 <strong>Modo Inseguro</strong> — Sistema Vulnerable
                    <span style="font-weight:400;color:#dc2626;"> | SQLi: ACTIVO | Rate Limiting: OFF | CSRF: OFF</span>
                @endif
            </div>
        </div>

        {{-- Error de login --}}
        @if($errors->has('login'))
            <div class="alert-danger">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ $errors->first('login') }}</span>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════
             FORMULARIO DE LOGIN
             @csrf → DynamicCsrfMiddleware decide si verificar o no
             username → en modo inseguro acepta payload SQLi:
               admin' OR '1'='1' --
        ═════════════════════════════════════════════════════════ --}}
        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="form-group">
                <label>Usuario</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    <input type="text" name="username" class="form-control"
                        value="{{ old('username') }}"
                        placeholder="Ingrese su usuario"
                        required autocomplete="off">
                </div>
                @if(!$modoSeguroLogin)
                    <span class="hint-vuln" style="background:#fff7ed;color:#c2410c;">
                        ⚠ SQLi activo — payload: <code>admin' OR '1'='1' --</code>
                    </span>
                @endif
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </span>
                    <input type="password" name="password" class="form-control"
                        placeholder="Ingrese su contraseña"
                        required>
                </div>
                @if(!$modoSeguroLogin)
                    <span class="hint-vuln" style="background:#fff7ed;color:#c2410c;">
                        ⚠ Sin rate limiting — fuerza bruta sin restricción
                    </span>
                @endif
            </div>

            <button type="submit" class="btn-login">
                <span>Iniciar Sesión</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>

        <div class="footer-login">
            <div>Sistema de Gestión Médica © 2026</div>
            <div class="nist-badge">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Protegido bajo NIST SP 800-53</span>
            </div>
        </div>

    </div>
</div>

</body>
</html>