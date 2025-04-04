<?php
require_once '../modelos/modelo_acc_programa.php';

class ControladorAcc_programa {
    private $modelo;
    private $es_vista;

    public function __construct() {
        $this->modelo = new ModeloAcc_programa();
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

    public function obtenerTodos($registrosPorPagina, $pagina, $busqueda = '') {
        $offset = ($pagina - 1) * $registrosPorPagina;
        return $this->modelo->obtenerTodos($registrosPorPagina, $offset, $busqueda);
    }

    public function obtenerPorId($id) {
        return $this->modelo->obtenerPorId($id);
    }

    public function buscar($termino, $registrosPorPagina, $offset) {
        return $this->modelo->buscar($termino, $registrosPorPagina, $offset);
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
            $filename = "acc_programa_export_{$timestamp}";
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
$controlador = new ControladorAcc_programa();

switch ($accion) {
    case 'crear':
        $datos = $_POST;
        $resultado = $controlador->crear($datos);
        echo json_encode($resultado);
        break;

    case 'actualizar':
        $id = $_POST['id_programas']; // Usar el campo de llave primaria
        $datos = $_POST;
        unset($datos['id_programas']); // Eliminar el ID de los datos
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
        $registrosPorPagina = $_GET['registrosPorPagina'] ?? 10; // Número de registros por página
        $paginaActual = $_GET['pagina'] ?? 1; // Página actual
        $offset = ($paginaActual - 1) * $registrosPorPagina; // Calcular el offset
        $totalRegistros = $controlador->contarRegistrosPorBusqueda($termino); // Contar registros que coinciden con la búsqueda
        $totalPaginas = ceil($totalRegistros / $registrosPorPagina); // Calcular total de páginas
        $resultado = $controlador->buscar($termino, $registrosPorPagina, $offset);
        // Aquí debes incluir la vista con los resultados
        include '../vistas/vista_acc_programa.php'; // Incluir la vista correspondiente
        break;

    case 'exportar':
        $formato = $_GET['formato'] ?? 'excel';
        $controlador->exportar($formato);
        break;

    default:
        $registrosPorPagina = (int)($_GET['registrosPorPagina'] ?? 10); // Asegúrate de que sea un entero
        $paginaActual = (int)($_GET['pagina'] ?? 1); // Asegúrate de que sea un entero
        $offset = ($paginaActual - 1) * $registrosPorPagina; // Calcular el offset
        $registros = $controlador->obtenerTodos($registrosPorPagina, $paginaActual);
        include '../vistas/vista_acc_programa.php'; // Incluir la vista correspondiente
        break;
}
