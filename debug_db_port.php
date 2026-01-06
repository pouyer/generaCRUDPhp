<?php
header('Content-Type: text/plain');

$host = '127.0.0.1';
$port = 3308; // Puerto asumido por el error

echo "=== Diagnostico de Puerto DB ===\n";
echo "Intentando conectar a $host:$port...\n";

$socket = @fsockopen($host, $port, $errno, $errstr, 2);

if ($socket) {
    echo "EXITO: El puerto $port está ABIERTO y escuchando.\n";
    echo "Tu base de datos está funcionando. El error podría ser de credenciales.\n";
    fclose($socket);
} else {
    echo "FALLO: No se pudo conectar a $host:$port.\n";
    echo "Error: $errstr ($errno)\n";
    echo "DIAGNOSTICO: Tu servicio de Base de Datos (Docker/MySQL) NO está escuchando en el puerto $port.\n";
    echo "Verifica que el contenedor esté encendido.\n";
}

echo "\n--- Prueba con Localhost ---\n";
$socket2 = @fsockopen('localhost', $port, $errno2, $errstr2, 2);
if ($socket2) {
    echo "EXITO con 'localhost'.\n";
    fclose($socket2);
} else {
    echo "FALLO con 'localhost': $errstr2 ($errno2)\n";
}
?>
