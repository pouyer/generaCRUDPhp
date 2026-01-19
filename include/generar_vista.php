<?php
require_once __DIR__ . '/funciones_utilidades.php';

function generar_vista($tabla, $campos, $directorio, $es_vista, $config = []) {
    $nombreClase = ucfirst($tabla);
    
    // Función interna para identificar campos que deben excluirse de los modales (auditoría/automáticos)
    $esCampoExcluido = function($campo) {
        $nombre = strtolower($campo['Field']);
        $extra = strtolower($campo['Extra']);
        $default = strtolower($campo['Default'] ?? '');
        $key = strtoupper($campo['Key']);

        // 1. Excluir siempre Claves Primarias de los formularios (se manejan vía hidden o auto)
        if ($key == 'PRI' || $extra == 'auto_increment') return true;

        // 2. Excluir timestamps automáticos (insensible a mayúsculas y paréntesis)
        if (strpos($extra, 'current_timestamp') !== false) return true;
        if (strpos($default, 'current_timestamp') !== false) return true;

        // 3. Excluir por nombre de campo de auditoría común
        $nombresNegros = [
            'fecha_insercion', 'fecha_actualizacion', 'fecha_registro', 
            'fecha_creacion', 'fecha_crea', 'fecha_actualiza',
            'usuario_id_inserto', 'usuario_id_actualizo', 'usuario_id_creo',
            'usuario_id_crea', 'usuario_id_actualiza'
        ];
        if (in_array($nombre, $nombresNegros)) return true;

        return false;
    };

    // Filtrar campos para formulario Crear
    $camposValidosCrear = array_filter($campos, function($campo) use ($esCampoExcluido) {
        return !$esCampoExcluido($campo);
    });

    // Filtrar campos para formulario Actualizar
    $camposValidosActualizar = array_filter($campos, function($campo) use ($esCampoExcluido) {
        return !$esCampoExcluido($campo);
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
