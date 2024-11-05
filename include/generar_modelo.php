<?php
function generar_modelo($tabla, $campos, $directorio, $archivo_conexion) {
    // Obtener información de la llave primaria
    global $conexion;
    $query = "SHOW COLUMNS FROM $tabla";
    $resultado = $conexion->query($query);
    $llavePrimaria = '';
    $tipoPrimaria = '';
    $camposAutoIncrement = [];
    $camposRequeridos = [];

    while ($columna = $resultado->fetch_assoc()) {
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
    }

    if (empty($llavePrimaria)) {
        throw new Exception("No se encontró llave primaria en la tabla $tabla");
    }

    $nombreClase = ucfirst($tabla);
    $contenido = "<?php\n";
    $contenido .= "require_once '../$archivo_conexion';\n\n";
    $contenido .= "class Modelo$nombreClase {\n";
    $contenido .= "    private \$conexion;\n";
    $contenido .= "    private \$llavePrimaria = '$llavePrimaria';\n\n";
    
    // Constructor
    $contenido .= "    public function __construct() {\n";
    $contenido .= "        global \$conexion;\n";
    $contenido .= "        \$this->conexion = \$conexion;\n";
    $contenido .= "    }\n\n";
    
    // Obtener todos los registros
    $contenido .= "    public function obtenerTodos() {\n";
    $contenido .= "        \$query = \"SELECT * FROM $tabla\";\n";
    $contenido .= "        \$resultado = \$this->conexion->query(\$query);\n";
    $contenido .= "        return \$resultado ? \$resultado->fetch_all(MYSQLI_ASSOC) : false;\n";
    $contenido .= "    }\n\n";
    
    // Obtener un registro por llave primaria
    $contenido .= "    public function obtenerPorId(\$id) {\n";
    $contenido .= "        \$query = \"SELECT * FROM $tabla WHERE \$this->llavePrimaria = ?\";\n";
    $contenido .= "        \$stmt = \$this->conexion->prepare(\$query);\n";
    $contenido .= "        \$stmt->bind_param('$tipoPrimaria', \$id);\n";
    $contenido .= "        \$stmt->execute();\n";
    $contenido .= "        \$resultado = \$stmt->get_result();\n";
    $contenido .= "        return \$resultado ? \$resultado->fetch_assoc() : false;\n";
    $contenido .= "    }\n\n";
    
    // Crear un nuevo registro
    $contenido .= "    public function crear(\$datos) {\n";
    $contenido .= "        \$campos = [];\n";
    $contenido .= "        \$valores = [];\n";
    $contenido .= "        \$tipos = '';\n";
    $contenido .= "        \$params = [];\n\n";
    
    foreach ($campos as $campo) {
        if ($campo['Field'] != $llavePrimaria && !in_array($campo['Field'], $camposAutoIncrement)) {  // Excluimos la llave primaria y auto_increment
            if (in_array($campo['Field'], $camposRequeridos)) { // Solo requeridos
                $contenido .= "        if (empty(\$datos['{$campo['Field']}'])) {\n";
                $contenido .= "            throw new Exception('El campo {$campo['Field']} es requerido.');\n";
                $contenido .= "        }\n";
            }
            $contenido .= "        if (isset(\$datos['{$campo['Field']}'])) {\n";
            $contenido .= "            \$campos[] = '{$campo['Field']}';\n";
            $contenido .= "            \$valores[] = '?';\n";
            $contenido .= "            \$params[] = \$datos['{$campo['Field']}'];\n";
            
            // Determinar el tipo de dato para bind_param
            if (strpos($campo['Type'], 'int') !== false) {
                $contenido .= "            \$tipos .= 'i';\n";
            } elseif (strpos($campo['Type'], 'float') !== false || 
                     strpos($campo['Type'], 'double') !== false || 
                     strpos($campo['Type'], 'decimal') !== false) {
                $contenido .= "            \$tipos .= 'd';\n";
            } elseif (strpos($campo['Type'], 'date') !== false || strpos($campo['Type'], 'datetime') !== false) {
                $contenido .= "            // Formatear fecha\n";
                $contenido .= "            \$params[] = date('Y-m-d', strtotime(\$datos['{$campo['Field']}']));\n";
                $contenido .= "            \$tipos .= 's';\n"; // Asumimos que se envía como string
            } else {
                $contenido .= "            \$tipos .= 's';\n";
            }
            
            $contenido .= "        }\n";
        }
    }
    
    $contenido .= "\n        \$query = \"INSERT INTO $tabla (\" . implode(', ', \$campos) . \") VALUES (\" . implode(', ', \$valores) . \")\";\n";
    $contenido .= "        \$stmt = \$this->conexion->prepare(\$query);\n";
    $contenido .= "        if (!empty(\$params)) {\n";
    $contenido .= "            \$stmt->bind_param(\$tipos, ...\$params);\n";
    $contenido .= "        }\n";
    $contenido .= "        return \$stmt->execute();\n";
    $contenido .= "    }\n\n";
    
    // Actualizar un registro
    $contenido .= "    public function actualizar(\$id, \$datos) {\n";
    $contenido .= "        error_log(print_r(\$datos, true)); // Verificar los datos recibidos\n";
    $contenido .= "        \$actualizaciones = [];\n";
    $contenido .= "        \$tipos = '$tipoPrimaria'; // Para la llave primaria\n";
    $contenido .= "        \$params = [];\n\n";
    
    foreach ($campos as $campo) {
        if ($campo['Field'] != $llavePrimaria) {
            if (in_array($campo['Field'], $camposRequeridos)) { // Solo requeridos
                $contenido .= "        if (empty(\$datos['{$campo['Field']}'])) {\n";
                $contenido .= "            throw new Exception('El campo {$campo['Field']} es requerido.');\n";
                $contenido .= "        }\n";
            }
            $contenido .= "        if (isset(\$datos['{$campo['Field']}'])) {\n";
            $contenido .= "            \$actualizaciones[] = \"{$campo['Field']} = ?\";\n";
            $contenido .= "            \$params[] = \$datos['{$campo['Field']}'];\n";
            
            if (strpos($campo['Type'], 'int') !== false) {
                $contenido .= "            \$tipos .= 'i';\n";
            } elseif (strpos($campo['Type'], 'float') !== false || 
                     strpos($campo['Type'], 'double') !== false || 
                     strpos($campo['Type'], 'decimal') !== false) {
                $contenido .= "            \$tipos .= 'd';\n";
            } elseif (strpos($campo['Type'], 'date') !== false || strpos($campo['Type'], 'datetime') !== false) {
                $contenido .= "            // Formatear fecha\n";
                $contenido .= "            \$params[] = date('Y-m-d', strtotime(\$datos['{$campo['Field']}']));\n";
                $contenido .= "            \$tipos .= 's';\n"; // Asumimos que se envía como string
            } else {
                $contenido .= "            \$tipos .= 's';\n";
            }
            
            $contenido .= "        }\n";
        }
    }
    
    $contenido .= "\n        \$params[] = \$id;\n";
    $contenido .= "        \$query = \"UPDATE $tabla SET \" . implode(', ', \$actualizaciones) . \" WHERE \$this->llavePrimaria = ?\";\n";
    $contenido .= "        \$stmt = \$this->conexion->prepare(\$query);\n";
    $contenido .= "        if (!empty(\$params)) {\n";
    $contenido .= "            \$stmt->bind_param(\$tipos, ...\$params);\n";
    $contenido .= "        }\n";
    $contenido .= "        return \$stmt->execute();\n";
    $contenido .= "    }\n\n";
    
    // Eliminar un registro
    $contenido .= "    public function eliminar(\$id) {\n";
    $contenido .= "        \$query = \"DELETE FROM $tabla WHERE \$this->llavePrimaria = ?\";\n";
    $contenido .= "        \$stmt = \$this->conexion->prepare(\$query);\n";
    $contenido .= "        \$stmt->bind_param('$tipoPrimaria', \$id);\n";
    $contenido .= "        return \$stmt->execute();\n";
    $contenido .= "    }\n";
    
    $contenido .= "}\n?>";

    $archivo = "$directorio/modelo_$tabla.php";
    return file_put_contents($archivo, $contenido) !== false;
}
?>
