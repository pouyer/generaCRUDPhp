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
?>
