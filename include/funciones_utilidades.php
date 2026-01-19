<?php
/**
 * Funciones de utilidad general para el generador de CRUD
 */

/**
 * Renderiza una plantilla PHP con los datos proporcionados
 * @param string $plantilla Ruta al archivo de plantilla
 * @param array $datos Array asociativo de variables para la plantilla
 * @return string El contenido renderizado
 */
function render_template($plantilla, $datos = []) {
    if (!file_exists($plantilla)) {
        throw new Exception("No se encontró la plantilla: $plantilla");
    }
    
    // Extraer variables para que estén disponibles en la plantilla
    extract($datos);
    
    // Iniciar buffer de salida
    ob_start();
    
    // Incluir plantilla
    include $plantilla;
    
    // Obtener contenido y limpiar buffer
    return ob_get_clean();
}

/**
 * Normaliza una ruta de archivo eliminando barras duplicadas y estandarizando separadores
 * @param string $ruta La ruta a normalizar
 * @return string Ruta normalizada
 */
function normalizar_ruta($ruta) {
    // Convertir barras invertidas a barras normales
    $ruta = str_replace('\\', '/', $ruta);
    // Eliminar barras diagonales múltiples
    $ruta = preg_replace('#/+#', '/', $ruta);
    // Eliminar barra diagonal final
    return rtrim($ruta, '/');
}

/**
 * Carga variables de entorno desde un archivo .env
 * @param string $ruta Ruta al archivo .env
 * @return bool true si se cargó, false si no existe
 */
function cargar_env($ruta) {
    if (!file_exists($ruta)) {
        return false;
    }
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        if (strpos(trim($linea), '#') === 0) continue;
        list($nombre, $valor) = explode('=', $linea, 2);
        $nombre = trim($nombre);
        $valor = trim($valor);
        putenv(sprintf('%s=%s', $nombre, $valor));
        $_ENV[$nombre] = $valor;
        $_SERVER[$nombre] = $valor;
    }
    return true;
}

/**
 * Verifica si una ruta existe y es accesible
 * @param string $ruta La ruta a verificar
 * @return bool|string true si es válida, mensaje de error si no
 */
function validar_ruta($ruta) {
    if (!file_exists($ruta)) {
        return "La ruta no existe: $ruta";
    }
    if (!is_readable($ruta)) {
        return "La ruta no es legible: $ruta";
    }
    if (!is_writable($ruta)) {
        return "La ruta no tiene permisos de escritura: $ruta";
    }
    return true;
}

// Función para copiar carpetas recursivamente
function copiarCarpeta($origen, $destino) {
    if (!is_dir($destino)) {
        mkdir($destino, 0777, true);
    }
    $archivos = scandir($origen);
    foreach ($archivos as $archivo) {  
        if ($archivo != '.' && $archivo != '..') {
            $rutaOrigen = "$origen/$archivo";
            $rutaDestino = "$destino/$archivo";
            if (is_dir($rutaOrigen)) {
                copiarCarpeta($rutaOrigen, $rutaDestino);
            } else {
                copy($rutaOrigen, $rutaDestino);
            }
        }
    }
}

// Función para copiar un archivo a una carpeta de destino
function copiarArchivo($origen, $destino) {
    // Verificar si el archivo de origen existe
    if (!file_exists($origen)) {
        return "El archivo no existe: $origen";
    }
    // Crear el directorio de destino si no existe
    $directorioDestino = dirname($destino);
    if (!is_dir($directorioDestino)) {
        mkdir($directorioDestino, 0777, true);
    }
    // Copiar el archivo
    if (copy($origen, $destino)) {
        return true;
    } else {
        return "Error al copiar el archivo: $origen a $destino";
    }
}

// Función para reemplazar una cadena en un archivo
function reemplazarEnArchivo($archivo, $cadena_a_busca, $palabra_reemplazo) {
    // Verificar si el archivo existe
    if (!file_exists($archivo)) {
        return "El archivo no existe: $archivo";
    }
    // Leer el contenido del archivo
    $contenido = file_get_contents($archivo);
    // Reemplazar la cadena
    $nuevoContenido = str_replace($cadena_a_busca, $palabra_reemplazo, $contenido);
    // Escribir el nuevo contenido en el archivo
    file_put_contents($archivo, $nuevoContenido);
    
    return true;
}

// Función para listar archivos en un directorio que coincidan con un patrón
function listarArchivos($directorio, $patron) {
    // Verificar si el directorio existe
    if (!is_dir($directorio)) {
        return "El directorio no existe: $directorio";
    }  
    // Obtener todos los archivos que coinciden con el patrón
    $archivos = glob("$directorio/$patron");
    
    return $archivos;
}

function crearArchivo($archivo, $contenido) {
    $fp = fopen($archivo, 'w');
    fwrite($fp, $contenido);
    fclose($fp);
}

function sincronizar_programas_vistas($conexion, $ruta_vistas, $ruta_relativa = null) {
    if (!is_dir($ruta_vistas)) return false;

    // 1. Validar/Crear Módulo 101 si no existe
    $id_modulo_default = 101;
    $conexion->query("INSERT IGNORE INTO acc_modulo (id_modulo, nombre_modulo, icono, orden, estado) 
                      VALUES ($id_modulo_default, 'Nuevos CRUDs', 'icon-plus-circled', 999, 'activo')");

    // 2. Escanear archivos PHP en vistas
    $archivos = glob($ruta_vistas . "/vista_*.php");
    foreach ($archivos as $ruta_archivo) {
        $nombre_archivo = basename($ruta_archivo);
        
        // Verificar si existe el programa
        $res_check = $conexion->query("SELECT id_programas FROM acc_programa WHERE nombre_archivo = '$nombre_archivo'");
        
        if ($res_check && $res_check->num_rows == 0) {
            // INSERT: Nuevo programa detectado
            $nombre_menu = str_replace(['vista_', '.php'], '', $nombre_archivo);
            $nombre_menu = ucwords(str_replace('_', ' ', $nombre_menu));
            
            $ruta_val = $ruta_relativa ? "'$ruta_relativa'" : "NULL";
            $sql_ins = "INSERT INTO acc_programa (nombre_menu, nombre_archivo, id_modulo, estado, icono, ruta) 
                        VALUES ('$nombre_menu', '$nombre_archivo', $id_modulo_default, 'activo', 'icon-dot-circled', $ruta_val)";
            
            if ($conexion->query($sql_ins)) {
                $id_prog = $conexion->insert_id;
                $conexion->query("INSERT IGNORE INTO acc_programa_x_rol (id_programas, id_rol, permiso_insertar, permiso_actualizar, permiso_eliminar, permiso_exportar) 
                                 VALUES ($id_prog, 1, 1, 1, 1, 1)");
            }
        } else {
            // UPDATE: Programa existente, actualizamos su ruta si se proporcionó una nueva
            if ($ruta_relativa) {
                $sql_upd = "UPDATE acc_programa SET ruta = '$ruta_relativa' WHERE nombre_archivo = '$nombre_archivo'";
                $conexion->query($sql_upd);
            }
        }
    }
    
    // 3. Asegurar que el modulo 101 esté activo
    $conexion->query("UPDATE acc_modulo SET estado = 'activo' WHERE id_modulo = 101");
    
    return true;
}

/**
 * Actualiza las rutas de los programas BASE de forma inteligente.
 * SOLO afecta a los programas administrativos internos (Módulos 1-4).
 */
function actualizaRutaProgramas($proyecto, $conexion) {
    $response = [];
    
    // Ruta para programas ADMINISTRATIVOS base (Módulos de Accesos: Roles, Usuarios, etc.)
    // Estos se encuentran SIEMPRE en la carpeta 'accesos/vistas'
    $rutaAcceso = '/' . $proyecto . '/accesos/vistas';
    
    // Solo actualizamos los módulos 1, 2, 3, 4 (Core del sistema)
    $queryAcceso = "UPDATE `acc_programa` SET `ruta` = ? WHERE id_modulo IN (1, 2, 3, 4)";
    $stmtAcceso = $conexion->prepare($queryAcceso);
    if ($stmtAcceso) {
        $stmtAcceso->bind_param('s', $rutaAcceso);
        $stmtAcceso->execute();
        $stmtAcceso->close();
    }

    $response['success'] = "Rutas de programas base sincronizadas.";
    return $response;
}

function verificar_tabla_configuracion($conexion) {
    if (!$conexion) return false;
    $sql = "CREATE TABLE IF NOT EXISTS acc_configuracion_objeto (
        id_config INT AUTO_INCREMENT PRIMARY KEY,
        nombre_proyecto VARCHAR(150) NULL,
        nombre_objeto VARCHAR(150) NOT NULL,
        tipo_objeto VARCHAR(20) DEFAULT 'TABLE',
        configuracion_json LONGTEXT NOT NULL,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY u_proyecto_objeto (nombre_proyecto, nombre_objeto)
    )";
    return $conexion->query($sql);
}
