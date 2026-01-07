<?php
/**
 * Plantilla para generar Modelos PHP
 * 
 * Variables disponibles:
 * @var string $tabla Nombre de la tabla
 * @var string $nombreClase Nombre de la clase (ucfirst)
 * @var string $archivo_conexion Nombre del archivo de conexión
 * @var string $llavePrimaria Nombre del campo de llave primaria
 * @var string $tipoPrimaria Tipo de dato de la llave primaria ('i', 'd', 's')
 * @var bool $es_vista Indica si es una vista
 * @var array $campos Array con información de las columnas
 * @var array $camposAutoIncrement Array de nombres de campos autoincrementables
 * @var array $camposRequeridos Array de nombres de campos requeridos
 * @var array $camposCURRENT Array de nombres de campos con default current_timestamp
 */
?>
<?php echo "<?php\n"; ?>
    /**
     * Modelo para la tabla <?php echo $tabla; ?>
     */

<?php
// Ordenar campos según la configuración si existe
if (isset($config['fields'])) {
    usort($campos, function($a, $b) use ($config) {
        $orderA = $config['fields'][$a['Field']]['order'] ?? 999;
        $orderB = $config['fields'][$b['Field']]['order'] ?? 999;
        return $orderA - $orderB;
    });
}
?>
require_once '../<?php echo $archivo_conexion; ?>';

class Modelo<?php echo $nombreClase; ?> {
    private $conexion;
    private $llavePrimaria = '<?php echo $llavePrimaria; ?>';
    private $es_vista = <?php echo $es_vista ? 'true' : 'false'; ?>;

    public function __construct() {
        global $conexion;
        $this->conexion = $conexion;
    }

    // Métodos para obtener datos relacionados (Comboboxes)
<?php foreach ($relaciones as $campo => $config): ?>
    public function obtenerRelacionado_<?php echo $campo; ?>() {
        $sql = "SELECT `<?php echo $config['parentid']; ?>` as id, `<?php echo $config['display']; ?>` as texto FROM `<?php echo $config['parent']; ?>` ORDER BY `<?php echo $config['display']; ?>` ASC";
        $resultado = $this->conexion->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }
<?php endforeach; ?>

    // Función para contar registros
    public function contarRegistros() {
        $query = "SELECT COUNT(*) as total FROM <?php echo $tabla; ?>";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : 0;
    }

    // Función de contarRegistrosPorBusqueda
    public function contarRegistrosPorBusqueda($termino) {
        $query = "SELECT COUNT(*) as total FROM <?php echo $tabla; ?> ";
<?php foreach ($relaciones as $campo => $config): ?>
        $query .= " LEFT JOIN `<?php echo $config['parent']; ?>` ON `<?php echo $tabla; ?>`.`<?php echo $campo; ?>` = `<?php echo $config['parent']; ?>`.`<?php echo $config['parentid']; ?>` ";
<?php endforeach; ?>
        $query .= " WHERE ";
<?php
    $camposBusqueda = [];
    foreach ($campos as $campo) {
        $camposBusqueda[] = "`{$tabla}`.`{$campo['Field']}`";
    }
    foreach ($relaciones as $campo => $config) {
        $camposBusqueda[] = "`{$config['parent']}`.`{$config['display']}`";
    }
?>
        $query .= "CONCAT_WS(' ', <?php echo implode(', ', $camposBusqueda); ?>) LIKE ?";
        $stmt = $this->conexion->prepare($query);
        $termino = "%" . $termino . "%";
        $stmt->bind_param('s', $termino);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : false;
    }

    // Obtener todos los registros
    public function obtenerTodos($registrosPorPagina, $offset, $orderBy = '<?php echo $llavePrimaria; ?>', $orderDir = 'DESC') {
        // Validar columnas permitidas para evitar inyección SQL
        $allowedColumns = [<?php 
            $allCols = array_map(function($c) use($tabla) { return "'`{$tabla}`.`{$c['Field']}`'"; }, $campos);
            foreach($relaciones as $c => $r) {
                $allCols[] = "'`{$r['parent']}`.`{$r['display']}`'";
            }
            echo implode(', ', $allCols);
        ?>];
        
        // Limpiar el nombre de la columna para la validación
        $orderByClean = str_replace(['`', ' '], '', $orderBy);
        $isValid = false;
        foreach($allowedColumns as $ac) {
            if (str_replace(['`', ' '], '', $ac) === $orderByClean) {
                $isValid = true;
                break;
            }
        }
        
        $orderSQL = $isValid ? " ORDER BY $orderBy $orderDir " : " ORDER BY `<?php echo $tabla; ?>`.`<?php echo $llavePrimaria; ?>` DESC ";

        $query = "SELECT `<?php echo $tabla; ?>`.* <?php 
            foreach($relaciones as $campo => $config) {
                echo ", `{$config['parent']}`.`{$config['display']}` as `{$campo}_display` ";
            }
        ?> FROM <?php echo $tabla; ?>";
<?php foreach ($relaciones as $campo => $config): ?>
        $query .= " LEFT JOIN `<?php echo $config['parent']; ?>` ON `<?php echo $tabla; ?>`.`<?php echo $campo; ?>` = `<?php echo $config['parent']; ?>`.`<?php echo $config['parentid']; ?>` ";
<?php endforeach; ?>
        $query .= $orderSQL;
        $query .= " LIMIT ? OFFSET ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $registrosPorPagina, $offset);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : false;
    }

<?php if (!$es_vista): ?>
    // Obtener un registro por llave primaria
    public function obtenerPorId($id) {
        $query = "SELECT `<?php echo $tabla; ?>`.* <?php 
            foreach($relaciones as $campo => $config) {
                echo ", `{$config['parent']}`.`{$config['display']}` as `{$campo}_display` ";
            }
        ?> FROM <?php echo $tabla; ?>";
<?php foreach ($relaciones as $campo => $config): ?>
        $query .= " LEFT JOIN `<?php echo $config['parent']; ?>` ON `<?php echo $tabla; ?>`.`<?php echo $campo; ?>` = `<?php echo $config['parent']; ?>`.`<?php echo $config['parentid']; ?>` ";
<?php endforeach; ?>
        $query .= " WHERE `<?php echo $tabla; ?>`.$this->llavePrimaria = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('<?php echo $tipoPrimaria; ?>', $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc() : false;
    }

    // Crear un nuevo registro
    public function crear($datos) {
        $campos = [];
        $valores = [];
        $tipos = '';
        $params = [];

<?php foreach ($campos as $campo): ?>
<?php 
    if ($campo['Field'] != $llavePrimaria && !in_array($campo['Field'], $camposAutoIncrement) && !in_array($campo['Field'], $camposCURRENT)): 
        $fieldName = $campo['Field'];
        $fieldType = $campo['Type'];
?>
<?php if (in_array($fieldName, $camposRequeridos)): ?>
        if (!isset($datos['<?php echo $fieldName; ?>']) || $datos['<?php echo $fieldName; ?>'] === '') {
            throw new Exception('El campo <?php echo $fieldName; ?> es requerido.');
        } elseif (isset($datos['<?php echo $fieldName; ?>'])) {
            $campos[] = '`<?php echo $fieldName; ?>`';
            $valores[] = '?';
<?php else: ?>
        if (isset($datos['<?php echo $fieldName; ?>']) && $datos['<?php echo $fieldName; ?>'] !== '') {
          if (isset($datos['<?php echo $fieldName; ?>'])) {
            $campos[] = '`<?php echo $fieldName; ?>`';
            $valores[] = '?';
<?php endif; ?>
<?php
            // Lógica de tipos
            if (strpos($fieldType, 'int') !== false) {
                echo "            \$params[] = \$datos['$fieldName'];\n";
                echo "            \$tipos .= 'i';\n";
            } elseif (strpos($fieldType, 'float') !== false || strpos($fieldType, 'double') !== false || strpos($fieldType, 'decimal') !== false) {
                echo "            \$params[] = \$datos['$fieldName'];\n";
                echo "            \$tipos .= 'd';\n";
            } elseif (strpos($fieldType, 'date') !== false || strpos($fieldType, 'datetime') !== false) {
                echo "            // Formatear fecha\n";
                echo "            \$params[] = date('Y-m-d', strtotime(\$datos['$fieldName']));\n";
                echo "            \$tipos .= 's';\n";
            } else {
                echo "            \$params[] = \$datos['$fieldName'];\n";
                echo "            \$tipos .= 's';\n";
            }
?>
        <?php if (!in_array($fieldName, $camposRequeridos)): ?>  }<?php endif; ?>
        }
<?php endif; ?>
<?php endforeach; ?>

        $query = "INSERT INTO <?php echo $tabla; ?> (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $valores) . ")";
        $stmt = $this->conexion->prepare($query);
        if (!empty($params)) {
             if(!$stmt) throw new Exception("Error preparando insert: " . $this->conexion->error);
            $stmt->bind_param($tipos, ...$params);
        }
        return $stmt->execute();
    }

    // Actualizar un registro
    public function actualizar($id, $datos) {
        error_log(print_r($datos, true)); // Verificar los datos recibidos
        $actualizaciones = [];
        $tipos = '';
        $tipos_pk = '<?php echo $tipoPrimaria; ?>'; // Para la llave primaria
        $params = [];

<?php foreach ($campos as $campo): ?>
<?php 
    if ($campo['Field'] != $llavePrimaria && !in_array($campo['Field'], $camposAutoIncrement) && !in_array($campo['Field'], $camposCURRENT)): 
        $fieldName = $campo['Field'];
        $fieldType = $campo['Type'];
?>
<?php if (in_array($fieldName, $camposRequeridos)): ?>
<?php 
    // Si el campo es de auditoría TIPO INSERT, no se debe validar como REQUERIDO en el ACTUAIZAR
    $isAuditInsert = (isset($config['fields'][$fieldName]['audit']) && $config['fields'][$fieldName]['audit'] === 'insert');
?>
<?php if ($isAuditInsert): ?>
        // Campo de auditoria (Usuario Inserta) - No se requiere en actualización
        if (isset($datos['<?php echo $fieldName; ?>']) && $datos['<?php echo $fieldName; ?>'] !== '') {
            $actualizaciones[] = "`<?php echo $fieldName; ?>` = ?";
<?php else: ?>
        if (!isset($datos['<?php echo $fieldName; ?>']) || $datos['<?php echo $fieldName; ?>'] === '') {
            throw new Exception('El campo <?php echo $fieldName; ?> es requerido.');
        } elseif (isset($datos['<?php echo $fieldName; ?>'])) {
            $actualizaciones[] = "`<?php echo $fieldName; ?>` = ?";
<?php endif; ?>
<?php else: ?>
        // Para campos No requeridos
        if (isset($datos['<?php echo $fieldName; ?>']) && ($datos['<?php echo $fieldName; ?>'] !== '' || $datos['<?php echo $fieldName; ?>'] === 0)) {
            $actualizaciones[] = "`<?php echo $fieldName; ?>` = ?";
<?php endif; ?>
<?php
            // Lógica de tipos
            if (strpos($fieldType, 'int') !== false) {
                echo "            \$params[] = \$datos['$fieldName'];\n";
                echo "            \$tipos .= 'i';\n";
            } elseif (strpos($fieldType, 'float') !== false || strpos($fieldType, 'double') !== false || strpos($fieldType, 'decimal') !== false) {
                echo "            \$params[] = \$datos['$fieldName'];\n";
                echo "            \$tipos .= 'd';\n";
            } elseif (strpos($fieldType, 'date') !== false || strpos($fieldType, 'datetime') !== false) {
                echo "            // Formatear fecha\n";
                echo "            \$params[] = date('Y-m-d', strtotime(\$datos['$fieldName']));\n";
                echo "            \$tipos .= 's';\n";
            } else {
                echo "            \$params[] = \$datos['$fieldName'];\n";
                echo "            \$tipos .= 's';\n";
            }
?>
        }
<?php endif; ?>
<?php endforeach; ?>

        $params[] = $id;
        $tipos .= $tipos_pk;
        $query = "UPDATE <?php echo $tabla; ?> SET " . implode(', ', $actualizaciones) . " WHERE $this->llavePrimaria = ?";
        $stmt = $this->conexion->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($tipos, ...$params);
        }
        return $stmt->execute();
    }

    // Eliminar un registro
    public function eliminar($id) {
        $query = "DELETE FROM <?php echo $tabla; ?> WHERE $this->llavePrimaria = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('<?php echo $tipoPrimaria; ?>', $id);
        return $stmt->execute();
    }
<?php endif; ?>

    // Función de búsqueda (reutilizada)
    public function buscar($termino, $registrosPorPagina, $offset, $orderBy = '<?php echo $llavePrimaria; ?>', $orderDir = 'DESC') {
        // Validar columnas permitidas
        $allowedColumns = [<?php 
            echo implode(', ', $allCols); // Reutilizar array de arriba
        ?>];
        
        $orderByClean = str_replace(['`', ' '], '', $orderBy);
        $isValid = false;
        foreach($allowedColumns as $ac) {
            if (str_replace(['`', ' '], '', $ac) === $orderByClean) {
                $isValid = true;
                break;
            }
        }
        
        $orderSQL = $isValid ? " ORDER BY $orderBy $orderDir " : " ORDER BY `<?php echo $tabla; ?>`.`<?php echo $llavePrimaria; ?>` DESC ";

        $query = "SELECT `<?php echo $tabla; ?>`.* <?php 
            foreach($relaciones as $campo => $config) {
                echo ", `{$config['parent']}`.`{$config['display']}` as `{$campo}_display` ";
            }
        ?> FROM <?php echo $tabla; ?>";
<?php foreach ($relaciones as $campo => $config): ?>
        $query .= " LEFT JOIN `<?php echo $config['parent']; ?>` ON `<?php echo $tabla; ?>`.`<?php echo $campo; ?>` = `<?php echo $config['parent']; ?>`.`<?php echo $config['parentid']; ?>` ";
<?php endforeach; ?>
        $query .= " WHERE ";
<?php
    // $camposBusqueda ya fue definida arriba en contarRegistrosPorBusqueda
?>
        $query .= "CONCAT_WS(' ', <?php echo implode(', ', $camposBusqueda); ?>) LIKE ?";
        $query .= $orderSQL;
        $query .= " LIMIT ? OFFSET ?";
        
        $stmt = $this->conexion->prepare($query);
        $termino = "%" . $termino . "%";
        $stmt->bind_param('sii', $termino, $registrosPorPagina, $offset);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : false;
    }

    // --- Métodos para Vistas (Búsqueda por Campo) ---

    public function contarPorCampo($campo, $valor) {
        // Validar campo
        $allowedColumns = [<?php 
            echo implode(', ', $allCols); 
        ?>];
        // También permitir columnas simples sin prefijo de tabla (para el select de la vista)
        $simpleCols = [<?php 
             echo implode(', ', array_map(function($c) { return "'{$c['Field']}'"; }, $campos));
        ?>];
        
        $campoLimpio = str_replace(['`', ' '], '', $campo);
        $esValido = false;
        $columnaSQL = '';

        // Mapear input simple a columna calificada
        foreach ($simpleCols as $idx => $sc) {
            if ($sc === $campo) {
                 $esValido = true;
                 $columnaSQL = "`<?php echo $tabla; ?>`.`" . $campo . "`";
                 break;
            }
        }
        if (!$esValido) {
             foreach($allowedColumns as $ac) {
                if (str_replace(['`', ' '], '', $ac) === $campoLimpio) {
                    $esValido = true;
                    $columnaSQL = $campo;
                    break;
                }
             }
        }

        if (!$esValido) return 0;

        $query = "SELECT COUNT(*) as total FROM <?php echo $tabla; ?> ";
<?php foreach ($relaciones as $campo => $config): ?>
        $query .= " LEFT JOIN `<?php echo $config['parent']; ?>` ON `<?php echo $tabla; ?>`.`<?php echo $campo; ?>` = `<?php echo $config['parent']; ?>`.`<?php echo $config['parentid']; ?>` ";
<?php endforeach; ?>
        $query .= " WHERE " . $columnaSQL . " LIKE ?";
        
        $stmt = $this->conexion->prepare($query);
        $valor = "%" . $valor . "%";
        $stmt->bind_param('s', $valor);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : 0;
    }

    public function buscarPorCampo($campo, $valor, $registrosPorPagina, $offset, $orderBy = '<?php echo $llavePrimaria; ?>', $orderDir = 'DESC') {
        // Validación de campo idéntica a contarPorCampo
         $allowedColumns = [<?php 
            echo implode(', ', $allCols); 
        ?>];
        $simpleCols = [<?php 
             echo implode(', ', array_map(function($c) { return "'{$c['Field']}'"; }, $campos));
        ?>];
        
        $campoLimpio = str_replace(['`', ' '], '', $campo);
        $esValido = false;
        $columnaSQL = '';

        foreach ($simpleCols as $idx => $sc) {
            if ($sc === $campo) {
                 $esValido = true;
                 $columnaSQL = "`<?php echo $tabla; ?>`.`" . $campo . "`";
                 break;
            }
        }
        if (!$esValido) {
             foreach($allowedColumns as $ac) {
                if (str_replace(['`', ' '], '', $ac) === $campoLimpio) {
                    $esValido = true;
                    $columnaSQL = $campo;
                    break;
                }
             }
        }
        if (!$esValido) return [];

        // Validación OrderBy
        $orderByClean = str_replace(['`', ' '], '', $orderBy);
        $orderValid = false;
        foreach($allowedColumns as $ac) {
            if (str_replace(['`', ' '], '', $ac) === $orderByClean) {
                $orderValid = true;
                break;
            }
        }
        $orderSQL = $orderValid ? " ORDER BY $orderBy $orderDir " : " ORDER BY `<?php echo $tabla; ?>`.`<?php echo $llavePrimaria; ?>` DESC ";

        $query = "SELECT `<?php echo $tabla; ?>`.* <?php 
            foreach($relaciones as $campo => $config) {
                echo ", `{$config['parent']}`.`{$config['display']}` as `{$campo}_display` ";
            }
        ?> FROM <?php echo $tabla; ?>";
<?php foreach ($relaciones as $campo => $config): ?>
        $query .= " LEFT JOIN `<?php echo $config['parent']; ?>` ON `<?php echo $tabla; ?>`.`<?php echo $campo; ?>` = `<?php echo $config['parent']; ?>`.`<?php echo $config['parentid']; ?>` ";
<?php endforeach; ?>
        $query .= " WHERE " . $columnaSQL . " LIKE ?";
        $query .= $orderSQL;
        $query .= " LIMIT ? OFFSET ?";
        
        $stmt = $this->conexion->prepare($query);
        $valor = "%" . $valor . "%";
        $stmt->bind_param('sii', $valor, $registrosPorPagina, $offset);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : false;
    }

    // Funcion de exportar datos
    public function exportarDatos($termino = '', $campoFiltro = '') {
        try {
            $query = "SELECT <?php 
                $exportFields = [];
                foreach ($campos as $c) {
                    $fn = $c['Field'];
                    $showExp = (isset($config['fields'][$fn]['export'])) ? $config['fields'][$fn]['export'] : true;
                    if ($showExp) $exportFields[] = "`{$tabla}`.`{$fn}`";
                }
                // Si por algún motivo no hay campos, al menos poner *
                echo empty($exportFields) ? '*' : implode(', ', $exportFields);

                foreach($relaciones as $campo => $configRel) {
                    $showExpBase = (isset($config['fields'][$campo]['export'])) ? $config['fields'][$campo]['export'] : true;
                    if ($showExpBase) {
                        echo ", `{$configRel['parent']}`.`{$configRel['display']}` as `{$campo}_display` ";
                    }
                }
            ?> FROM <?php echo $tabla; ?>";
<?php foreach ($relaciones as $campo => $configRel): ?>
            $query .= " LEFT JOIN `<?php echo $configRel['parent']; ?>` ON `<?php echo $tabla; ?>`.`<?php echo $campo; ?>` = `<?php echo $configRel['parent']; ?>`.`<?php echo $configRel['parentid']; ?>` ";
<?php endforeach; ?>
            $query .= " WHERE ";
            
            $usarFiltroCampo = false;
            $columnaSQL = '';

            if (!empty($campoFiltro)) {
                 // Validar campo
                $allowedColumns = [<?php 
                    echo implode(', ', $allCols); 
                ?>];
                $simpleCols = [<?php 
                     echo implode(', ', array_map(function($c) { return "'{$c['Field']}'"; }, $campos));
                ?>];
                
                $campoLimpio = str_replace(['`', ' '], '', $campoFiltro);
                
                foreach ($simpleCols as $idx => $sc) {
                    if ($sc === $campoFiltro) {
                         $usarFiltroCampo = true;
                         $columnaSQL = "`<?php echo $tabla; ?>`.`" . $campoFiltro . "`";
                         break;
                    }
                }
                if (!$usarFiltroCampo) {
                     foreach($allowedColumns as $ac) {
                        if (str_replace(['`', ' '], '', $ac) === $campoLimpio) {
                            $usarFiltroCampo = true;
                            $columnaSQL = $campoFiltro;
                            break;
                        }
                     }
                }
            }

            if ($usarFiltroCampo) {
                 $query .= $columnaSQL . " LIKE ?";
            } else {
<?php
    $camposBusqueda = [];
    foreach ($campos as $campo) {
        $camposBusqueda[] = "`{$tabla}`.`{$campo['Field']}`";
    }
    foreach ($relaciones as $campo => $config) {
        $camposBusqueda[] = "`{$config['parent']}`.`{$config['display']}`";
    }
?>
                $query .= "CONCAT_WS(' ', <?php echo implode(', ', $camposBusqueda); ?>) LIKE ?";
            }

            if (!$this->conexion) {
                throw new Exception('Error: No hay conexión a la base de datos');
            }

            $stmt = $this->conexion->prepare($query);
            if (!$stmt) {
                throw new Exception('Error preparando la consulta: ' . $this->conexion->error);
            }

            $terminoBusqueda = empty($termino) ? '%' : '%' . $termino . '%';
            $stmt->bind_param('s', $terminoBusqueda);
            if (!$stmt->execute()) {
                throw new Exception('Error ejecutando la consulta: ' . $stmt->error);
            }

            $resultado = $stmt->get_result();
            $datos = $resultado->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $datos;
        } catch (Exception $e) {
            error_log('Error en exportarDatos: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerEstados() {
        $tabla = '<?php echo $tabla; ?>';
        $sql = "SELECT estado, nombre_estado FROM acc_estado where tabla = ? and visible = 1 order by orden";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('s', $tabla);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $estados = [];

        if ($resultado && $resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $estados[] = $fila;
            }
        }
        return $estados;
    }
}
<?php echo "?>"; ?>
