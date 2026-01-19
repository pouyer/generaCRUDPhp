<?php
session_start();

if (isset($_POST['ruta'])) {
    $_SESSION['ruta'] = $_POST['ruta'];
}
if (isset($_POST['nombre_archivo'])) {
    $_SESSION['nombre_archivo'] = $_POST['nombre_archivo'];
} else {
    $_SESSION['nombre_archivo'] = 'conexion.php';
}
if (isset($_POST['base_datos'])) {
    $_SESSION['base_datos'] = $_POST['base_datos'];
}
if (isset($_POST['nombre_proyecto'])) {
    $_SESSION['nombre_proyecto'] = $_POST['nombre_proyecto'];
}
if (isset($_POST['admin_email'])) {
    $_SESSION['admin_email'] = $_POST['admin_email'];
}
if (isset($_POST['config_tablas'])) {
    $_SESSION['config_tablas'] = $_POST['config_tablas'];
    
    // Persistir en base de datos si hay conexión
    require_once 'conexion.php';
    require_once 'funciones_utilidades.php';
    
    if (isset($conexion)) {
        verificar_tabla_configuracion($conexion);
        $tablasConfig = json_decode($_POST['config_tablas'], true);
        $proyecto = (!empty($_SESSION['nombre_proyecto'])) ? $_SESSION['nombre_proyecto'] : null;
        
        foreach ($tablasConfig as $tabla => $config) {
            $configJson = json_encode($config);
            $sql = "INSERT INTO acc_configuracion_objeto (nombre_proyecto, nombre_objeto, configuracion_json) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE configuracion_json = VALUES(configuracion_json)";
            $stmt = $conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('sss', $proyecto, $tabla, $configJson);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

// Nueva acción: Cargar configuración desde BD
if (isset($_POST['accion']) && $_POST['accion'] === 'cargar_config' && isset($_POST['tabla'])) {
    require_once 'conexion.php';
    require_once 'funciones_utilidades.php';
    $tabla = $_POST['tabla'];
    $proyecto = (!empty($_SESSION['nombre_proyecto'])) ? $_SESSION['nombre_proyecto'] : null;
    
    if (isset($conexion)) {
        verificar_tabla_configuracion($conexion);
        // Ajustar consulta para manejar NULL correctamente
        $sql = "SELECT configuracion_json FROM acc_configuracion_objeto WHERE nombre_objeto = ? AND (nombre_proyecto <=> ?)";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('ss', $tabla, $proyecto);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($fila = $res->fetch_assoc()) {
                header('Content-Type: application/json');
                echo $fila['configuracion_json'];
                exit;
            }
            $stmt->close();
        }
    }
    echo json_encode(['error' => 'No config found']);
    exit;
}
?>