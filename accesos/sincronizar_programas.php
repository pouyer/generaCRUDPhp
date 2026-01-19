<?php
session_start();
header('Content-Type: application/json');
include_once "../include/funciones_utilidades.php";

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

    // 1. Calcular la ruta relativa de forma robusta (Ej: /Proyecto/vistas/...)
    // Obtenemos la ruta base (padre del proyecto) para calcular el path relativo
    $ruta_proyecto_base = str_replace('\\', '/', $_SESSION['ruta']); // Ej: C:/xampp/htdocs/MiProyecto
    $ruta_padre = dirname($ruta_proyecto_base); // Ej: C:/xampp/htdocs
    
    $ruta_norm = str_replace('\\', '/', $ruta_sinc);
    $ruta_relativa = str_replace($ruta_padre, '', $ruta_norm); // Resultado: /MiProyecto/vistas/...

    // Obtenemos solo el nombre del proyecto para actualizaRutaProgramas si es necesario
    $rutaArray = explode('/', rtrim($ruta_proyecto_base, '/'));
    $proyecto = end($rutaArray);

    // 2. Sincronizar programas usando la función centralizada
    // Pasamos explícitamente la ruta_relativa para que se asigne a los archivos encontrados
    if (sincronizar_programas_vistas($conexion, $ruta_sinc, $ruta_relativa)) {
        
        // 3. ACTUALIZACIÓN DE RUTAS BASE (Módulos 1-4)
        // Solo actualizamos los módulos core de accesos
        actualizaRutaProgramas($proyecto, $conexion);

        echo json_encode([
            'success' => true,
            'message' => "Sincronización completada exitosamente. Se han registrado nuevos programas y se han validado las rutas de acceso de forma inteligente."
        ]);
        exit;
    } else {
        throw new Exception("Error al sincronizar la carpeta.");
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
