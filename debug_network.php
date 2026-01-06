<?php
header('Content-Type: text/plain');

$host = 'smtp.gmail.com';
$port = 587;

echo "=== Diagnostico de Red PHP ===\n";
echo "Host: $host\n";
echo "Puerto: $port\n\n";

// 1. Prueba de Resolución de DNS (IPv4)
echo "1. gethostbyname('$host'): ";
$ip = gethostbyname($host);
if ($ip != $host) {
    echo "OK -> $ip\n";
} else {
    echo "FALLO (No resolvió la IP)\n";
}

// 2. Prueba DNS detallada
echo "\n2. dns_get_record('$host'):\n";
try {
    $records = dns_get_record($host, DNS_A);
    print_r($records);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// 3. Prueba de Conexión Socket
echo "\n3. stream_socket_client (tcp://$host:$port):\n";
$socketContext = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
$socket = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $socketContext);

if ($socket) {
    echo "CONEXION EXITOSA.\n";
    fwrite($socket, "EHLO " . gethostname() . "\r\n");
    echo "Respuesta Servidor: " . fread($socket, 512) . "\n";
    fclose($socket);
} else {
    echo "ERROR DE CONEXION: $errstr ($errno)\n";
}

// 4. Prueba con IP Directa (si se resolvio antes)
if ($ip != $host) {
    echo "\n4. Prueba con IP directa ($ip):\n";
    $socket = @stream_socket_client("tcp://$ip:$port", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $socketContext);
    if ($socket) {
        echo "CONEXION CON IP EXITOSA.\n";
        fclose($socket);
    } else {
        echo "ERROR CON IP: $errstr ($errno)\n";
    }
} else {
    echo "\n4. Omitiendo prueba de IP (no se resolvio DNS).\n";
}
?>
