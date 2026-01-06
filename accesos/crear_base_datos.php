<?php
session_start();
header('Content-Type: application/json');

try {
    // Incluir el archivo de conexión
    if (!isset($_SESSION['ruta']) || !isset($_SESSION['nombre_archivo'])) {
        throw new Exception("Configuración de conexión no encontrada");
    }

    $archivo_conexion = $_SESSION['ruta'] . '/' . $_SESSION['nombre_archivo'];
    if (!file_exists($archivo_conexion)) {
        throw new Exception("Archivo de conexión no encontrado");
    }

    require_once($archivo_conexion);
    
    // Verificar la conexión
    if (!isset($conexion)) {
        throw new Exception("Error en la conexión a la base de datos");
    }

    // Deshabilitar verificación de claves foráneas
    $conexion->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Leer el archivo SQL
    $archivo_sql = __DIR__ . '/SQL_accesos/CreaTablasAcceso.sql';
    if (!file_exists($archivo_sql)) {
        throw new Exception("Archivo SQL no encontrado");
    }

    // Leer y ejecutar el archivo SQL
    $sql = file_get_contents($archivo_sql);
    
    // Array para almacenar errores no críticos
    $errores_no_criticos = [];
    
    // Dividir el script en sentencias individuales
    $sentencias = array_filter(
        explode(';', $sql),
        'trim'
    );

    // Ejecutar cada sentencia
    foreach ($sentencias as $sentencia) {
        $sentencia = trim($sentencia);
        if (empty($sentencia)) continue;

        // Ejecutar la sentencia y manejar errores específicos
        $resultado = $conexion->query($sentencia);
        
        if (!$resultado) {
            $error_code = $conexion->errno;
            $error_message = $conexion->error;
            
            // Errores que podemos ignorar
            if (in_array($error_code, [1051, 4092])) {
                $errores_no_criticos[] = "Aviso: $error_message (Código: $error_code)";
                continue;
            }
            
            // Error crítico
            throw new Exception("Error en sentencia SQL ($error_code): $error_message\nSentencia: $sentencia");
        }
    }

    // Habilitar verificación de claves foráneas
    $conexion->query("SET FOREIGN_KEY_CHECKS = 1");

    // Asegúrate de que no se envíe texto adicional antes del JSON
    ob_clean(); // Limpia el buffer de salida
    echo json_encode([
        'success' => true,
        'message' => "Tablas de acceso creadas exitosamente",
        'warnings' => $errores_no_criticos
    ]);

    // Actualizar el correo del administrador si está en la sesión
    if (isset($_SESSION['admin_email']) && !empty($_SESSION['admin_email'])) {
        $admin_email = $conexion->real_escape_string($_SESSION['admin_email']);
        $conexion->query("UPDATE acc_usuario SET correo = '$admin_email' WHERE username = 'admin'");
    }

} catch (Exception $e) {
    ob_clean(); // Limpia el buffer de salida
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
