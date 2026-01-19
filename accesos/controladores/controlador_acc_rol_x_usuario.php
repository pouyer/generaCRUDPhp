<?php
require_once '../modelos/modelo_acc_rol_x_usuario.php';

class ControladorAcc_rol_x_usuario {
    private $modelo;
    private $es_vista;

    public function __construct() {
        $this->modelo = new ModeloAcc_rol_x_usuario();
        $this->es_vista = false;
    }

    public function crear($datos) {
        return $this->modelo->crear($datos);
    }

    public function actualizar($id, $datos) {
        return $this->modelo->actualizar($id, $datos);
    }

    public function eliminar($id) {
        return $this->modelo->eliminar($id);
    }

    public function obtenerTodos($registrosPorPagina, $pagina, $sort = null, $dir = 'ASC') {
        $offset = ($pagina - 1) * $registrosPorPagina;
        return $this->modelo->obtenerTodos($registrosPorPagina, $offset, $sort, $dir);
    }

    public function obtenerPorId($id) {
        return $this->modelo->obtenerPorId($id);
    }

    public function buscar($termino, $registrosPorPagina, $offset, $sort = null, $dir = 'ASC') {
        return $this->modelo->buscar($termino, $registrosPorPagina, $offset, $sort, $dir);
    }

    public function contarRegistrosPorBusqueda($termino) {
         return $this->modelo->contarRegistrosPorBusqueda($termino);
    }

    public function exportar($formato) {
        try {
            $termino = $_GET['busqueda'] ?? '';
            $datos = $this->modelo->exportarDatos($termino);
            if ($datos === false) {
                throw new Exception('Error al obtener los datos para exportar');
            }
            if (empty($datos)) {
                throw new Exception('No hay datos para exportar');
            }

            $timestamp = date('Y-m-d_H-i-s');
            $filename = "acc_rol_x_usuario_export_{$timestamp}";
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
        echo "<table border='1'>\n";
        if (!empty($datos)) {
            echo "<tr>\n";
            foreach (array_keys($datos[0]) as $campo) {
                echo "<th>" . htmlspecialchars($campo) . "</th>\n";
            }
            echo "</tr>\n";
        }
        foreach ($datos as $fila) {
            echo "<tr>\n";
            foreach ($fila as $valor) {
                echo "<td>" . htmlspecialchars($valor) . "</td>\n";
            }
            echo "</tr>\n";
        }
        echo "</table>";
    }

    private function exportarCSV($datos) {
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
        if (!empty($datos)) {
            echo implode("\t", array_keys($datos[0])) . "\n";
            foreach ($datos as $fila) {
                echo implode("\t", $fila) . "\n";
            }
        }
    }

}

$accion = $_GET['action'] ?? '';
$controlador = new ControladorAcc_rol_x_usuario();

switch ($accion) {
    case 'crear':
        $datos = $_POST;
        $resultado = $controlador->crear($datos);
        echo json_encode($resultado);
        break;

    case 'actualizar':
        $id = $_POST['id_usuario']; // Usar el campo de llave primaria
        $datos = $_POST;
        unset($datos['id_usuario']); // Eliminar el ID de los datos
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
        $registrosPorPagina = $_GET['registrosPorPagina'] ?? 10;
        $paginaActual = $_GET['pagina'] ?? 1;
        $sort = isset($_GET['sort']) ? str_replace(['`', ' '], '', $_GET['sort']) : null;
        $dir = $_GET['dir'] ?? 'ASC';
        $offset = ($paginaActual - 1) * $registrosPorPagina;
        $totalRegistros = $controlador->contarRegistrosPorBusqueda($termino);
        $resultado = $controlador->buscar($termino, $registrosPorPagina, $offset, $sort, $dir);
        $registros = $resultado; // Para la vista
        include '../vistas/vista_acc_rol_x_usuario.php';
        break;

    case 'exportar':
        $formato = $_GET['formato'] ?? 'excel';
        $controlador->exportar($formato);
        break;

    default:
        $registrosPorPagina = (int)($_GET['registrosPorPagina'] ?? 10);
        $paginaActual = (int)($_GET['pagina'] ?? 1);
        $sort = isset($_GET['sort']) ? str_replace(['`', ' '], '', $_GET['sort']) : null;
        $dir = $_GET['dir'] ?? 'ASC';
        $offset = ($paginaActual - 1) * $registrosPorPagina;

        if (isset($_GET['busqueda'])) {
             $termino = $_GET['busqueda'];
             $totalRegistros = $controlador->contarRegistrosPorBusqueda($termino);
             $registros = $controlador->buscar($termino, $registrosPorPagina, $offset, $sort, $dir);
        } else {
             $totalRegistros = (new ModeloAcc_rol_x_usuario())->contarRegistros();
             $registros = $controlador->obtenerTodos($registrosPorPagina, $paginaActual, $sort, $dir);
        }
        include '../vistas/vista_acc_rol_x_usuario.php';
        break;
}
