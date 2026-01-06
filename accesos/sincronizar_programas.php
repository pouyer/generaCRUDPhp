<?php
session_start();
header('Content-Type: application/json');

try {
    // Verificar requerimientos básicos
    if (!isset($_POST['ruta_sinc']) || empty($_POST['ruta_sinc'])) {
        throw new Exception("La ruta de sincronización es requerida.");
    }

    $ruta_sinc = $_POST['ruta_sinc'];
    if (!is_dir($ruta_sinc)) {
        throw new Exception("La ruta no es un directorio válido: $ruta_sinc");
    }

    // Incluir archivo de conexión del proyecto generado
    if (!isset($_SESSION['ruta']) || !isset($_SESSION['nombre_archivo'])) {
        throw new Exception("Configuración de conexión no encontrada en la sesión.");
    }

    $archivo_conexion = $_SESSION['ruta'] . '/' . $_SESSION['nombre_archivo'];
    if (!file_exists($archivo_conexion)) {
        throw new Exception("Archivo de conexión no encontrado: $archivo_conexion");
    }

    require_once($archivo_conexion);
    
    if (!isset($conexion)) {
        throw new Exception("Error en la conexión a la base de datos.");
    }

    // 1. Validar/Crear Módulo 101
    $id_modulo_default = 101;
    $check_mod = $conexion->query("SELECT id_modulo FROM acc_modulo WHERE id_modulo = $id_modulo_default");
    if ($check_mod->num_rows == 0) {
        $sql_ins_mod = "INSERT INTO acc_modulo (id_modulo, nombre_modulo, icono, orden, estado) 
                        VALUES ($id_modulo_default, 'No Asignado', 'icon-help-circled', 999, 'I')";
        if (!$conexion->query($sql_ins_mod)) {
            throw new Exception("Error al crear el módulo 101: " . $conexion->error);
        }
    }

    // 2. Escanear archivos PHP
    $archivos = glob($ruta_sinc . "/*.php");
    $conteo_procesados = 0;
    $conteo_nuevos = 0;

    foreach ($archivos as $ruta_archivo) {
        $nombre_archivo = basename($ruta_archivo);
        $conteo_procesados++;

        // Verificar si existe el programa
        $stmt_check = $conexion->prepare("SELECT id_programas FROM acc_programa WHERE nombre_archivo = ?");
        $stmt_check->bind_param("s", $nombre_archivo);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows == 0) {
            // No existe, insertar
            $nombre_menu = pathinfo($nombre_archivo, PATHINFO_FILENAME);
            $estado = 'I';
            
            $stmt_ins = $conexion->prepare("INSERT INTO acc_programa (nombre_menu, nombre_archivo, id_modulo, estado) VALUES (?, ?, ?, ?)");
            $stmt_ins->bind_param("ssis", $nombre_menu, $nombre_archivo, $id_modulo_default, $estado);
            
            if ($stmt_ins->execute()) {
                $conteo_nuevos++;
            }
            $stmt_ins->close();
        }
        $stmt_check->close();
    }

    echo json_encode([
        'success' => true,
        'message' => "Sincronización completada.",
        'totales' => [
            'procesados' => $conteo_procesados,
            'nuevos' => $conteo_nuevos
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
