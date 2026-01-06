<?php
require_once __DIR__ . '/funciones_utilidades.php';

function generar_vista($tabla, $campos, $directorio, $es_vista, $config = []) {
    $nombreClase = ucfirst($tabla);
    
    // Filtrar campos para formulario Crear
    $camposValidosCrear = array_filter($campos, function($campo) {
        return !($campo['Extra'] == 'auto_increment' || 
                 $campo['Extra'] == 'on update current_timestamp()' || 
                 $campo['Key'] == 'PRI' || 
                 $campo['Default'] == 'current_timestamp()');
    });

    // Filtrar campos para formulario Actualizar (excluyendo PK y timestamp auto)
    $camposValidosActualizar = array_filter($campos, function($campo) {
        return ($campo['Key'] != 'PRI' && 
                $campo['Default'] != 'current_timestamp()' && 
                $campo['Extra'] != 'on update current_timestamp()');
    });

    $datosPlantilla = [
        'tabla' => $tabla,
        'nombreClase' => $nombreClase,
        'es_vista' => $es_vista,
        'campos' => $campos,
        'camposValidosCrear' => $camposValidosCrear,
        'camposValidosActualizar' => $camposValidosActualizar,
        'relaciones' => $config['relaciones'] ?? [],
        'config' => $config
    ];

    try {
        $contenido = render_template(__DIR__ . '/../templates/vista.tpl.php', $datosPlantilla);
        $archivo = "$directorio/vista_$tabla.php";
        return file_put_contents($archivo, $contenido) !== false;
    } catch (Exception $e) {
        error_log("Error al generar vista para $tabla: " . $e->getMessage());
        return false;
    }
}

function generar_vista_css($directorio) {
    // Esta función es muy simple, no requiere plantilla compleja, pero podríamos mover el CSS a un archivo .css estático si quisieramos.
    // Por ahora lo dejamos igual pero optimizado.
    $contenido = "   body {
       font-size: 0.9rem; /* Ajustar el tamaño de fuente general */
   }
   .table th, .table td {
       font-size: 0.85rem; /* Ajustar el tamaño de fuente de la tabla */
   }";
    $archivo = "$directorio/estilos.css";
    return file_put_contents($archivo, $contenido) !== false;
}
?>
