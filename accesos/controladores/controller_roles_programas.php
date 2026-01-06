<?php
require_once '../modelos/modelo_roles_programas.php';
require_once '../modelos/modelo_acc_log.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ControllerRolesProgramas {
    private $modelo;

    public function __construct($conexion) {
        if (!$conexion) {
            // Intentar recuperar la conexión si no fue pasada pero el modelo la incluyó
            global $conexion;
        }
        $this->modelo = new ModeloRolesProgramas($conexion);
    }

    public function obtenerRoles() {
        return $this->modelo->obtenerRoles();
    }

    public function obtenerProgramasNoAsignados($id_rol) {
        return $this->modelo->obtenerProgramasNoAsignados((int)$id_rol);
    }

    public function obtenerProgramasAsignados($id_rol) {
        return $this->modelo->obtenerProgramasAsignados((int)$id_rol);
    }

    public function guardarCambios($id_rol, $programas) {
        return $this->modelo->guardarCambios((int)$id_rol, $programas);
    }
}

// Inicialización para llamadas directas (AJAX) o inclusiones
$accion = $_GET['action'] ?? '';

// El modelo ya incluye la conexión, así que $conexion debería estar definida globalmente
if (isset($conexion)) {
    $controller = new ControllerRolesProgramas($conexion);

    if ($accion) {
        header('Content-Type: application/json');
        switch ($accion) {
            case 'obtenerRoles':
                echo json_encode($controller->obtenerRoles());
                break;

            case 'guardarCambios':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $id_rol = $_POST['id_rol'] ?? null;
                    $programas = $_POST['programas'] ?? [];
                    $resultado = $controller->guardarCambios($id_rol, $programas);
                    if ($resultado && $id_rol) {
                        $log = new ModeloAcc_log();
                        $log->registrar($_SESSION['usuario_id'] ?? 0, 'ACTUALIZAR', 'roles_x_programa', "Permisos actualizados para Rol ID $id_rol. Total programas: " . count($programas));
                    }
                    echo json_encode($resultado);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
                }
                break;

            case 'obtenerProgramasNoAsignados':
                $id_rol = $_GET['id_rol'] ?? null;
                echo json_encode($controller->obtenerProgramasNoAsignados($id_rol));
                break;

            case 'obtenerProgramasAsignados':
                $id_rol = $_GET['id_rol'] ?? null;
                echo json_encode($controller->obtenerProgramasAsignados($id_rol));
                break;
        }
        exit;
    }
}
?>
