<?php
require_once __DIR__ . '/funciones_utilidades.php';

function generar_controlador($tabla, $campos, $directorio, $archivo_conexion, $es_vista, $config = []) {
    $nombreClase = ucfirst($tabla);
    $primaryKey = getPrimaryKey($campos);

    $datosPlantilla = [
        'tabla' => $tabla,
        'nombreClase' => $nombreClase,
        'primaryKey' => $primaryKey,
        'es_vista' => $es_vista,
        'campos' => $campos,
        'relaciones' => $config['relaciones'] ?? [],
        'config' => $config
    ];

    try {
        $contenido = render_template(__DIR__ . '/../templates/controlador.tpl.php', $datosPlantilla);
        $archivo = "$directorio/controlador_$tabla.php";
        return file_put_contents($archivo, $contenido) !== false;
    } catch (Exception $e) {
        error_log("Error al generar controlador para $tabla: " . $e->getMessage());
        return false;
    }
}

// Función para obtener el campo de llave primaria
function getPrimaryKey($campos) {
    foreach ($campos as $campo) {
        if ($campo['Key'] === 'PRI') {
            return $campo['Field'];
        }
    }
    return $campos[0]['Field'] ?? null; // Retorna el primer campo como fallback si no hay PK
}
?>