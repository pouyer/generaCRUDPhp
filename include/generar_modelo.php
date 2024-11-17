<?php
function generar_modelo($tabla, $campos, $directorio, $archivo_conexion) {
    // Obtener información de la llave primaria
    global $conexion;
    $query = "SHOW COLUMNS FROM $tabla"; // Línea 5
    $resultado = $conexion->query($query);
    $llavePrimaria = '';
    $tipoPrimaria = '';
    $camposAutoIncrement = [];
    $camposRequeridos = [];
    $camposCURRENT = [];

    while ($columna = $resultado->fetch_assoc()) { // Línea 12
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
    
    // Función para contar registros
    $contenido .= "    public function contarRegistros() {\n";
    $contenido .= "        \$query = \"SELECT COUNT(*) as total FROM $tabla\";\n";
    $contenido .= "        \$stmt = \$this->conexion->prepare(\$query);\n";
    $contenido .= "        \$stmt->execute();\n";
    $contenido .= "        \$resultado = \$stmt->get_result();\n";
    $contenido .= "        return \$resultado ? \$resultado->fetch_assoc()['total'] : 0;\n";
    $contenido .= "    }\n\n";
    
    // Función de contarRegistrosPorBusqueda
    $contenido .= "    public function contarRegistrosPorBusqueda(\$termino) {\n";
    $contenido .= "        \$query = \"SELECT COUNT(*) as total FROM $tabla WHERE \";\n";
    $contenido .= "        \$camposBusqueda = [];\n";
    foreach ($campos as $campo) {
        $contenido .= "        \$camposBusqueda[] = \"`{$campo['Field']}`\";\n"; // Agregar cada campo a un array
    }
    $contenido .= "        \$query .= \"CONCAT_WS(' ', \" . implode(', ', \$camposBusqueda) . \") LIKE ?\";\n"; // Usar el array para construir la consulta
    $contenido .= "        \$stmt = \$this->conexion->prepare(\$query);\n";
    $contenido .= "        \$termino = \"%\" . \$termino . \"%\";\n";
    $contenido .= "        \$stmt->bind_param('s', \$termino);\n";
    $contenido .= "        \$stmt->execute();\n";
    $contenido .= "        \$resultado = \$stmt->get_result();\n";
    $contenido .= "        return \$resultado ? \$resultado->fetch_assoc()['total'] : false;\n";
    $contenido .= "    }\n";

    // Obtener todos los registros
    $contenido .= "    public function obtenerTodos(\$registrosPorPagina, \$offset) {\n";
    $contenido .= "        \$query = \"SELECT * FROM $tabla\";\n";
    $contenido .= "        \$query .= \" LIMIT ? OFFSET ?\";\n";
    $contenido .= "        \$stmt = \$this->conexion->prepare(\$query);\n";
    $contenido .= "        \$stmt->bind_param('ii', \$registrosPorPagina, \$offset);\n";
    $contenido .= "        \$stmt->execute();\n";
    $contenido .= "        \$resultado = \$stmt->get_result();\n";
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
        if ($campo['Field'] != $llavePrimaria && !in_array($campo['Field'], $camposAutoIncrement) && !in_array($campo['Field'], $camposCURRENT) ) {  // Excluimos la llave primaria, auto_increment y CURRENT_TIMESTAMP
            if (in_array($campo['Field'], $camposRequeridos)) { // Solo requeridos
                $contenido .= "        if (empty(\$datos['{$campo['Field']}'])) {\n";
                $contenido .= "            throw new Exception('El campo {$campo['Field']} es requerido.');\n";
                $contenido .= "        } elseif (isset(\$datos['{$campo['Field']}'])) {\n";
                $contenido .= "            \$campos[] = '`{$campo['Field']}`';\n";
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
            } else {
                $contenido .= "        if (!empty(\$datos['{$campo['Field']}'])) {\n";
                $contenido .= "          if (isset(\$datos['{$campo['Field']}'])) {\n";
                $contenido .= "            \$campos[] = '`{$campo['Field']}`';\n";
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
                
                $contenido .= "           }\n";
                $contenido .= "        }\n";    
            }   
          
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
    $contenido .= "        \$tipos = '';\n";
    $contenido .= "        \$tipos_pk = '$tipoPrimaria'; // Para la llave primaria\n";
    $contenido .= "        \$params = [];\n\n";
    foreach ($campos as $campo) {
        if ($campo['Field'] != $llavePrimaria && !in_array($campo['Field'], $camposAutoIncrement) && !in_array($campo['Field'], $camposCURRENT) ) {  // Excluimos la llave primaria, auto_increment y CURRENT_TIMESTAMP
            if (in_array($campo['Field'], $camposRequeridos)) { // Solo requeridos
                $contenido .= "        if (empty(\$datos['{$campo['Field']}'])) {\n";
                $contenido .= "            throw new Exception('El campo {$campo['Field']} es requerido.');\n";
                $contenido .= "        } elseif (isset(\$datos['{$campo['Field']}'])) {\n";
                $contenido .= "            \$actualizaciones[] = \"`{$campo['Field']}` = ?\";\n";
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

            } else { // para campos No requeridos
                $contenido .= "        if (!empty(\$datos['{$campo['Field']}'])) {\n";
                $contenido .= "            if (isset(\$datos['{$campo['Field']}'])) {\n";
                $contenido .= "            \$actualizaciones[] = \"`{$campo['Field']}` = ?\";\n";
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
                $contenido .= "        }\n";
            }
        }
    }
    $contenido .= "\n        \$params[] = \$id;\n";
    $contenido .= "        \$tipos .= \$tipos_pk;\n";
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
    
    // Función de búsqueda
    $contenido .= "    public function buscar(\$termino, \$registrosPorPagina, \$offset) {\n";
    $contenido .= "        \$query = \"SELECT * FROM $tabla WHERE \";\n";
    $contenido .= "        \$camposBusqueda = [];\n";
    foreach ($campos as $campo) {
        $contenido .= "        \$camposBusqueda[] = \"`{$campo['Field']}`\";\n"; // Agregar cada campo a un array
    }
    $contenido .= "        \$query .= \"CONCAT_WS(' ', \" . implode(', ', \$camposBusqueda) . \") LIKE ? LIMIT ? OFFSET ?\";\n"; // Usar el array para construir la consulta
    $contenido .= "        \$stmt = \$this->conexion->prepare(\$query);\n";
    $contenido .= "        \$termino = \"%\" . \$termino . \"%\";\n";
    //$contenido .= "         error_log('preparación de la consulta: ' . \$query .' Parametros termino: ' . \$termino .' registrosPorPagina: '.\$registrosPorPagina. ' offset: ' .\$offset); // Log para depuración\n" //quitar cuando funcione
   // $contenido .= "         error_log('preparación de la consulta: ' . \$query ); \n"; //quitar cuando funcione
    $contenido .= "        \$stmt->bind_param('sii', \$termino, \$registrosPorPagina, \$offset);\n";
    $contenido .= "        \$stmt->execute();\n";
    $contenido .= "        \$resultado = \$stmt->get_result();\n";
   // $contenido .= "         error_log('Resultado Condulta: ' . \$resultado); // Log para depuración\n" //quitar cuando funcione
    $contenido .= "        return \$resultado ? \$resultado->fetch_all(MYSQLI_ASSOC) : false;\n";
    $contenido .= "    }\n"; //fin funcion buscar

    // funcion de exportar datos a partir de las busquedas
    $contenido .= "    public function exportarDatos(\$termino = '') {\n";
    $contenido .= "        try {\n";
    $contenido .= "            \$query = \"SELECT * FROM $tabla WHERE \";\n";
    $contenido .= "            \$camposBusqueda = [];\n";
    foreach ($campos as $campo) {
        $contenido .= "            \$camposBusqueda[] = \"`{$campo['Field']}`\";\n";
    }
    $contenido .= "            \$query .= \"CONCAT_WS(' ', \" . implode(', ', \$camposBusqueda) . \") LIKE ?\";\n";
    $contenido .= "            if (!\$this->conexion) {\n";
    $contenido .= "                throw new Exception('Error: No hay conexión a la base de datos');\n";
    $contenido .= "            }\n\n";
    $contenido .= "            \$stmt = \$this->conexion->prepare(\$query);\n";
    $contenido .= "            if (!\$stmt) {\n";
    $contenido .= "                throw new Exception('Error preparando la consulta: ' . \$this->conexion->error);\n";
    $contenido .= "            }\n\n";
    $contenido .= "            \$terminoBusqueda = empty(\$termino) ? '%' : '%' . \$termino . '%';\n";
    $contenido .= "            \$stmt->bind_param('s', \$terminoBusqueda);\n";
    $contenido .= "            if (!\$stmt->execute()) {\n";
    $contenido .= "                throw new Exception('Error ejecutando la consulta: ' . \$stmt->error);\n";
    $contenido .= "            }\n\n";
    $contenido .= "            \$resultado = \$stmt->get_result();\n";
    $contenido .= "            \$datos = \$resultado->fetch_all(MYSQLI_ASSOC);\n";
    $contenido .= "            \$stmt->close();\n";
    $contenido .= "            return \$datos;\n";
    $contenido .= "        } catch (Exception \$e) {\n";
    $contenido .= "            error_log('Error en exportarDatos: ' . \$e->getMessage());\n";
    $contenido .= "            return false;\n";
    $contenido .= "        }\n";
    $contenido .= "    }\n\n"; //fin funcion exportarDatos

    $contenido .= "}\n?>"; //fin generacion clase modelo

    $archivo = "$directorio/modelo_$tabla.php";
    return file_put_contents($archivo, $contenido) !== false;
}
?>