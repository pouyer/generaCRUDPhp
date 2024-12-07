<?php
require_once '../modelos/modelo_roles_programas.php';

class ControllerRolesProgramas {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new ModeloRolesProgramas($conexion);
    }

    public function obtenerRoles() {
        return $this->modelo->obtenerRoles();
    }

    public function obtenerProgramasNoAsignados($id_rol) {
        return $this->modelo->obtenerProgramasNoAsignados($id_rol);
    }

    public function obtenerProgramasAsignados($id_rol) {
        return $this->modelo->obtenerProgramasAsignados($id_rol);
    }

    public function guardarCambios($id_rol, $programas) {
        return $this->modelo->guardarCambios($id_rol, $programas);
    }
}

// Manejo de acciones
$accion = $_GET['action'] ?? '';
$conexion = $conexion; // Asegúrate de que $conexion esté disponible
$controller = new ControllerRolesProgramas($conexion);

switch ($accion) {
    case 'obtenerRoles':
        $roles = $controller->obtenerRoles();
        echo json_encode($roles);
        break;

    case 'guardarCambios':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_rol = $_POST['id_rol'];
            $programas = $_POST['programas'];
            $resultado = $controller->guardarCambios($id_rol, $programas);
            echo json_encode($resultado);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        }
        break;

    case 'obtenerProgramasNoAsignados':
        $id_rol = $_GET['id_rol'] ?? null;
        $programasNoAsignados = $controller->obtenerProgramasNoAsignados($id_rol);
        echo json_encode($programasNoAsignados);
        break;

    case 'obtenerProgramasAsignados':
        $id_rol = $_GET['id_rol'] ?? null;
        $programasAsignados = $controller->obtenerProgramasAsignados($id_rol);
        echo json_encode($programasAsignados);
        break;

    default:
        // Manejar el caso por defecto
        //$roles = $controller->obtenerRoles(); // Obtener roles por defecto
        //echo json_encode($roles); // Devolver roles si no se especifica acción
        //header('Location: ../vistas/vista_roles_programas.php');
        break;
}
?>
