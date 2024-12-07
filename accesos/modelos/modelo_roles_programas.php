<?php
require_once '../../<conexion.php>';

class ModeloRolesProgramas {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerRoles() {
        $sql = "SELECT * FROM acc_rol";
        $resultado = $this->conexion->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerProgramasNoAsignados($id_rol) {
        $sql = "SELECT * FROM acc_programa WHERE id_programas NOT IN (SELECT id_programas FROM acc_programa_x_rol r WHERE r.id_rol = ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('i', $id_rol);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerProgramasAsignados($id_rol) {
        $sql = "SELECT p.* FROM acc_programa p JOIN acc_programa_x_rol pr ON p.id_programas = pr.id_programas WHERE pr.id_rol = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('i', $id_rol);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function guardarCambios($id_rol, $programas) {
        // Primero, eliminar las relaciones existentes para el rol
        $sqlDelete = "DELETE FROM acc_programa_x_rol WHERE id_rol = ?";
        $stmtDelete = $this->conexion->prepare($sqlDelete);
        $stmtDelete->bind_param('i', $id_rol);
        $stmtDelete->execute();

        // Luego, insertar las nuevas relaciones
        $sqlInsert = "INSERT INTO acc_programa_x_rol (id_programas, id_rol) VALUES (?, ?)";
        $stmtInsert = $this->conexion->prepare($sqlInsert);

        foreach ($programas as $id_programa) {
            $stmtInsert->bind_param('ii', $id_programa, $id_rol);
            $stmtInsert->execute();
        }

        return ['status' => 'success'];
    }
}
?> 