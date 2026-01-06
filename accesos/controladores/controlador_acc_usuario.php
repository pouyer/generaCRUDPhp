<?php
require_once '../modelos/modelo_acc_usuario.php';
require_once '../modelos/modelo_acc_log.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ControladorAcc_usuario {
    private $modelo;
    private $es_vista;

    public function __construct() {
        $this->modelo = new ModeloAcc_usuario();
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
            $filename = "acc_usuario_export_{$timestamp}";
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

    public function obtenerRoles($id_usuario) {
        return $this->modelo->obtenerRolesPorUsuario($id_usuario);
    }
    
    public function eliminarRol($id_usuario, $id_rol) {
        return $this->modelo->eliminarRol($id_usuario, $id_rol);
    }

    public function agregarRol($id_usuario, $id_rol) {
        return $this->modelo->agregarRol($id_usuario, $id_rol);
    }

}

$accion = $_GET['action'] ?? '';
$controlador = new ControladorAcc_usuario();

switch ($accion) {
    case 'crear':
        $datos = $_POST;
        $resultado = $controlador->crear($datos);
        if ($resultado) {
            $log = new ModeloAcc_log();
            $log->registrar($_SESSION['usuario_id'] ?? 0, 'CREAR', 'acc_usuario', "Usuario creado: " . ($datos['username'] ?? ''));
        }
        echo json_encode($resultado);
        break;

    case 'actualizar':
        $id = $_POST['id_usuario']; // Usar el campo de llave primaria
        $datos = $_POST;
        unset($datos['id_usuario']); // Eliminar el ID de los datos
        $resultado = $controlador->actualizar($id, $datos);
        if ($resultado) {
            $log = new ModeloAcc_log();
            $log->registrar($_SESSION['usuario_id'] ?? 0, 'ACTUALIZAR', 'acc_usuario', "Usuario ID $id actualizado");
        }
        echo json_encode($resultado);
        break;

    case 'eliminar':
        $id = $_POST['id'];
        $resultado = $controlador->eliminar($id);
        if ($resultado) {
            $log = new ModeloAcc_log();
            $log->registrar($_SESSION['usuario_id'] ?? 0, 'ELIMINAR', 'acc_usuario', "Usuario ID $id eliminado");
        }
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
        $id_usuario = $_GET['id_usuario'] ?? null; // Asegúrate de que $id_usuario esté definido
        error_log('ID de usuario: ' . print_r($id_usuario, true)); // Envía el valor a los registros de errores
        include '../vistas/vista_acc_usuario.php'; // Incluir la vista correspondiente
        break;

    case 'exportar':
        $formato = $_GET['formato'] ?? 'excel';
        $controlador->exportar($formato);
        break;
    
    case 'obtenerRoles':
        $id_usuario = $_GET['id_usuario'];
        $roles = $controlador->obtenerRoles($id_usuario);
        if ($roles) {
            foreach ($roles as $rol) {
                echo "<div class='d-flex justify-content-between align-items-center mb-2'>";
                echo "<span>" . htmlspecialchars($rol['nombre_rol']) . "</span>";
                echo "<button class='btn btn-danger' onclick='eliminarRol(" . $rol['id_rol'] . ", " . $id_usuario . ")'>Eliminar</button>";
                echo "</div>";
            }
        } else {
            echo "<div>No hay roles asignados.</div>";
        }
        break;

    case 'eliminarRol':
        $id_usuario = $_POST['id_usuario'];
        $id_rol = $_POST['id_rol'];
        $resultado = $controlador->eliminarRol($id_usuario, $id_rol);
        echo json_encode($resultado);
        break;

    case 'agregarRol':
        error_log(print_r($_POST, true)); // Esto te mostrará los datos recibidos
        if (isset($_POST['id_rol']) && isset($_POST['id_usuario_rol'])) {
            $id_usuario = $_POST['id_usuario_rol'];
            $id_rol = $_POST['id_rol'];
    
            if (empty($id_usuario) || empty($id_rol)) {
                echo json_encode(['error' => 'ID de usuario o rol no pueden estar vacíos.']);
                exit;
            }
    
            $resultado = $controlador->agregarRol($id_usuario, $id_rol);
            echo json_encode($resultado);
        } else {
            echo json_encode(['error' => 'Datos no recibidos.']);
        }
        break;
    
    case 'agregarRol':
        if (isset($_POST['id_rol']) && isset($_POST['id_usuario_rol'])) {
            $id_usuario = $_POST['id_usuario_rol'];
            $id_rol = $_POST['id_rol'];
    
            if (empty($id_usuario) || empty($id_rol)) {
                echo json_encode(['error' => 'ID de usuario o rol no pueden estar vacíos.']);
                exit;
            }
    
            $resultado = $controlador->agregarRol($id_usuario, $id_rol);
            echo json_encode($resultado);
        } else {
            echo json_encode(['error' => 'Datos no recibidos.']);
        }
        break;    

    default:
        $registrosPorPagina = (int)($_GET['registrosPorPagina'] ?? 10); // Asegúrate de que sea un entero
        $paginaActual = (int)($_GET['pagina'] ?? 1); // Asegúrate de que sea un entero
        $offset = ($paginaActual - 1) * $registrosPorPagina; // Calcular el offset
        $registros = $controlador->obtenerTodos($registrosPorPagina, $paginaActual);
        include '../vistas/vista_acc_usuario.php'; // Incluir la vista correspondiente
        break;
}
