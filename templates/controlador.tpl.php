<?php
/**
 * Plantilla para generar Controladores PHP
 * 
 * Variables disponibles:
 * @var string $nombreClase Nombre de la clase (ucfirst)
 * @var string $tabla Nombre de la tabla
 * @var string $primaryKey Nombre del campo llave primaria
 * @var bool $es_vista Indica si es una vista SQL
 */
?>
<?php echo "<?php\n"; ?>
require_once '../modelos/modelo_<?php echo $tabla; ?>.php';
if (file_exists('../modelos/modelo_acc_log.php')) {
    require_once '../modelos/modelo_acc_log.php';
} elseif (file_exists('../accesos/modelos/modelo_acc_log.php')) {
    require_once '../accesos/modelos/modelo_acc_log.php';
}

class Controlador<?php echo $nombreClase; ?> {
    private $modelo;
    private $modeloLog;
    private $es_vista;

    // Constructor
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->modelo = new Modelo<?php echo $nombreClase; ?>();
        $this->modeloLog = new ModeloAcc_log();
        $this->es_vista = <?php echo $es_vista ? 'true' : 'false'; ?>;
        
        // Cargar permisos
        $this->permisos = $_SESSION['permisos']['vista_<?php echo $tabla; ?>.php'] ?? ['ins' => 1, 'upd' => 1, 'del' => 1, 'exp' => 1];
    }

    private function verificarPermiso($clave) {
        if (!isset($this->permisos[$clave]) || !$this->permisos[$clave]) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tiene permisos para realizar esta acción']);
            exit;
        }
    }

<?php if (!$es_vista): ?>
    // Método para crear un nuevo registro    
    public function crear($datos) {
        $this->verificarPermiso('ins');
        $resultado = $this->modelo->crear($datos);
        if ($resultado) {
            $this->modeloLog->registrar($_SESSION['usuario_id'] ?? 0, 'CREATE', '<?php echo $tabla; ?>', 'Nuevo registro creado');
        }
        return $resultado;
    }

    // Método para actualizar un registro
    public function actualizar($id, $datos) {
        $this->verificarPermiso('upd');
        $resultado = $this->modelo->actualizar($id, $datos);
        if ($resultado) {
            $this->modeloLog->registrar($_SESSION['usuario_id'] ?? 0, 'UPDATE', '<?php echo $tabla; ?>', 'Registro actualizado ID: ' . $id);
        }
        return $resultado;
    }

    // Método para eliminar un registro
    public function eliminar($id) {
        $this->verificarPermiso('del');
        $resultado = $this->modelo->eliminar($id);
        if ($resultado) {
            $this->modeloLog->registrar($_SESSION['usuario_id'] ?? 0, 'DELETE', '<?php echo $tabla; ?>', 'Registro eliminado ID: ' . $id);
        }
        return $resultado;
    }
<?php endif; ?>

    // Método para obtener todos los registros
    public function obtenerTodos($registrosPorPagina, $pagina, $busqueda = '') {
        $offset = ($pagina - 1) * $registrosPorPagina;
        return $this->modelo->obtenerTodos($registrosPorPagina, $offset, $busqueda);
    }

    // Método para obtener un registro por ID
    public function obtenerPorId($id) {
        return $this->modelo->obtenerPorId($id);
    }

    // Método para buscar registros
    public function buscar($termino, $registrosPorPagina, $offset) {
        return $this->modelo->buscar($termino, $registrosPorPagina, $offset);
    }

    // Método para contar registros encontrados en la busqueda
    public function contarRegistrosPorBusqueda($termino) {
         return $this->modelo->contarRegistrosPorBusqueda($termino);
    }

    public function contarPorCampo($campo, $valor) {
        return $this->modelo->contarPorCampo($campo, $valor);
    }

    public function buscarPorCampo($campo, $valor, $registrosPorPagina, $offset) {
        return $this->modelo->buscarPorCampo($campo, $valor, $registrosPorPagina, $offset);
    }

    // funcion exportar en formatos Exel, CSV, TXT
    public function exportar($formato) {
        $this->verificarPermiso('exp');
        try {
            $termino = $_GET['busqueda'] ?? '';
            $campo = $_GET['campo'] ?? '';
            
            $logMsg = "Exportación a formato: $formato";
            if ($campo && $termino) {
                $logMsg .= " (Filtro: $campo = $termino)";
            } elseif ($termino) {
                $logMsg .= " (Búsqueda general: $termino)";
            }
            
            $this->modeloLog->registrar($_SESSION['usuario_id'] ?? 0, 'EXPORT', '<?php echo $tabla; ?>', $logMsg);
            
            $datos = $this->modelo->exportarDatos($termino, $campo);
            if ($datos === false) {
                throw new Exception('Error al obtener los datos para exportar');
            }
            if (empty($datos)) {
                throw new Exception('No hay datos para exportar');
            }

            $timestamp = date('Y-m-d_H-i-s');
            $filename = "<?php echo $tabla; ?>_export_{$timestamp}";
            if (!empty($termino)) {
                $filename .= "_busqueda_" . preg_replace('/[^a-zA-Z0-9]/', '_', $termino);
            }

            switch ($formato) {
                case 'excel':
                    header('Content-Type: application/vnd.ms-excel');
                    header("Content-Disposition: attachment; filename=\"$filename.xls\"");
                    $this->exportarExcel($datos);
                    break;

                case 'csv':
                    header('Content-Type: text/csv; charset=utf-8');
                    header("Content-Disposition: attachment; filename=\"$filename.csv\"");
                    $this->exportarCSV($datos);
                    break;

                case 'txt':
                    header('Content-Type: text/plain; charset=utf-8');
                    header("Content-Disposition: attachment; filename=\"$filename.txt\"");
                    $this->exportarTXT($datos);
                    break;
                default:
                    throw new Exception('Formato de exportación no válido');
            }
        } catch (Exception $e) {
            error_log('Error en exportación: ' . $e->getMessage());
            echo 'Error: ' . $e->getMessage();
            exit;
        }
        exit;
    }

    private function exportarExcel($datos) {
        // El formato Unicode UTF-16LE es el más compatible con Excel para caracteres especiales
        if (!empty($datos)) {
            $columnas = array_keys($datos[0]);
            $salida = implode("\t", $columnas) . "\n";
            
            foreach ($datos as $fila) {
                $salida .= implode("\t", $fila) . "\n";
            }
            
            // Enviamos el BOM para UTF-16LE
            echo "\xFF\xFE";
            // Convertimos la cadena de UTF-8 a UTF-16LE
            echo mb_convert_encoding($salida, 'UTF-16LE', 'UTF-8');
        }
    }

    private function exportarCSV($datos) {
        echo "\xEF\xBB\xBF"; // Add BOM for Excel UTF-8
        $output = fopen('php://output', 'w');
        if (!empty($datos)) {
            fputcsv($output, array_keys($datos[0]));
        }
        foreach ($datos as $fila) {
            fputcsv($output, $fila);
        }
        fclose($output);
    }

    private function exportarTXT($datos) {
        echo "\xEF\xBB\xBF"; // Add BOM for Excel UTF-8
        if (!empty($datos)) {
            echo implode("\t", array_keys($datos[0])) . "\n";
            foreach ($datos as $fila) {
                echo implode("\t", $fila) . "\n";
            }
        }
    }
}

// Manejo de las acciones
$accion = $_GET['action'] ?? '';
$controlador = new Controlador<?php echo $nombreClase; ?>();

switch ($accion) {
    case 'crear':
        $datos = $_POST;
        // Inyección automática de Auditoría de Usuario (Insert)
        <?php foreach ($config['fields'] as $campo => $conf): ?>
            <?php if (isset($conf['audit']) && ($conf['audit'] === 'insert' || $conf['audit'] === 'update')): ?>
                $datos['<?php echo $campo; ?>'] = $_SESSION['usuario_id'] ?? 0;
            <?php endif; ?>
        <?php endforeach; ?>
        $resultado = $controlador->crear($datos);
        echo json_encode($resultado);
        break;

    case 'actualizar':
        $id = $_POST['<?php echo $primaryKey; ?>']; // Usar el campo de llave primaria
        $datos = $_POST;
        // Inyección automática de Auditoría de Usuario (Update)
        <?php foreach ($config['fields'] as $campo => $conf): ?>
            <?php if (isset($conf['audit']) && $conf['audit'] === 'update'): ?>
                $datos['<?php echo $campo; ?>'] = $_SESSION['usuario_id'] ?? 0;
            <?php endif; ?>
        <?php endforeach; ?>
        unset($datos['<?php echo $primaryKey; ?>']); // Eliminar el ID de los datos
        $resultado = $controlador->actualizar($id, $datos);
        echo json_encode($resultado);
        break;

    case 'eliminar':
        $id = $_POST['id'];
        $resultado = $controlador->eliminar($id);
        echo json_encode($resultado);
        break;

    case 'buscar':
        $termino = $_GET['busqueda'] ?? '';
        $campoFiltro = $_GET['campo'] ?? '';
        $registrosPorPagina = $_GET['registrosPorPagina'] ?? 10;
        $paginaActual = $_GET['pagina'] ?? 1;
        $offset = ($paginaActual - 1) * $registrosPorPagina;
        
        if (!empty($campoFiltro) && !empty($termino)) {
            $totalRegistros = $controlador->contarPorCampo($campoFiltro, $termino);
            $resultado = $controlador->buscarPorCampo($campoFiltro, $termino, $registrosPorPagina, $offset);
        } else {
            $totalRegistros = $controlador->contarRegistrosPorBusqueda($termino);
            $resultado = $controlador->buscar($termino, $registrosPorPagina, $offset);
        }
        
        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
        // Aquí debes incluir la vista con los resultados
        include '../vistas/vista_<?php echo $tabla; ?>.php';
        break;

    case 'exportar':
        $formato = $_GET['formato'] ?? 'excel';
        $controlador->exportar($formato);
        break;

    default:
        $registrosPorPagina = (int)($_GET['registrosPorPagina'] ?? 10);
        $paginaActual = (int)($_GET['pagina'] ?? 1);
        $offset = ($paginaActual - 1) * $registrosPorPagina;
        $registros = $controlador->obtenerTodos($registrosPorPagina, $paginaActual);
        include '../vistas/vista_<?php echo $tabla; ?>.php';
        break;
}
