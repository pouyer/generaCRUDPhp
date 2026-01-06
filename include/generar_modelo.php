<?php
require_once __DIR__ . '/funciones_utilidades.php';

function generar_modelo($tabla, $campos, $directorio, $archivo_conexion, $es_vista, $config = []) {
    global $conexion;
    
    // Obtener información de la tabla y columnas
    $query = "SHOW COLUMNS FROM $tabla";
    $resultado = $conexion->query($query);
    
    $llavePrimaria = '';
    $tipoPrimaria = '';
    $camposAutoIncrement = [];
    $camposRequeridos = [];
    $camposCURRENT = [];
    $columnasInfo = []; // Array para pasar a la plantilla

    while ($columna = $resultado->fetch_assoc()) {
        $columnasInfo[] = $columna;
        
        if ($columna['Key'] == 'PRI') {
            $llavePrimaria = $columna['Field'];
            // Determinar el tipo de la llave primaria
            if (strpos($columna['Type'], 'int') !== false) {
                $tipoPrimaria = 'i';
            } elseif (strpos($columna['Type'], 'float') !== false || strpos($columna['Type'], 'double') !== false) {
                $tipoPrimaria = 'd';
            } else {
                $tipoPrimaria = 's';
            }
        }
        // Guardar campos auto_increment
        if ($columna['Extra'] == 'auto_increment') {
            $camposAutoIncrement[] = $columna['Field'];
        }
        // Guardar campos requeridos, excluyendo CURRENT_TIMESTAMP
        if ($columna['Null'] == 'NO' && $columna['Default'] !== 'current_timestamp()') {
            $camposRequeridos[] = $columna['Field'];
        }
        // Guardar campos CURRENT_TIMESTAMP
        if ($columna['Default'] == 'current_timestamp()') {
            $camposCURRENT[] = $columna['Field'];
        }
    }
    
    // Si no se encontró llave primaria (común en vistas), usar la primera columna como referencia para ordenamiento
    if (empty($llavePrimaria) && !empty($columnasInfo)) {
        $llavePrimaria = $columnasInfo[0]['Field'];
    }

    if (!$es_vista) {
        if (empty($llavePrimaria)) {
            throw new Exception("No se encontró llave primaria en la tabla $tabla");
        }
    }
    
    $nombreClase = ucfirst($tabla);
    
    // Datos para la plantilla
    $datosPlantilla = [
        'tabla' => $tabla,
        'nombreClase' => $nombreClase,
        'archivo_conexion' => $archivo_conexion,
        'llavePrimaria' => $llavePrimaria,
        'tipoPrimaria' => $tipoPrimaria,
        'es_vista' => $es_vista,
        'campos' => $columnasInfo,
        'camposAutoIncrement' => $camposAutoIncrement,
        'camposRequeridos' => $camposRequeridos,
        'camposCURRENT' => $camposCURRENT,
        'relaciones' => $config['relaciones'] ?? [],
        'config' => $config
    ];

    try {
        // Renderizar la plantilla
        $contenido = render_template(__DIR__ . '/../templates/modelo.tpl.php', $datosPlantilla);
        
        // Guardar archivo generado
        $archivo = "$directorio/modelo_$tabla.php";
        return file_put_contents($archivo, $contenido) !== false;
    } catch (Exception $e) {
        error_log("Error al generar modelo para $tabla: " . $e->getMessage());
        return false;
    }
}
?>