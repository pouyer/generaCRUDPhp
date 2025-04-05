<?php
/**
 * Funciones de utilidad general para el generador de CRUD
 */

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

?>
