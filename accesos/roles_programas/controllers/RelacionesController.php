<?php
require_once '../config.php'; // Asegúrate de incluir tu archivo de configuración

class RelacionesController {
    public function obtenerRoles() {
        global $pdo; // Usar la conexión PDO global

        $sql = "SELECT * FROM acc_rol";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProgramasNoAsignados($id_rol) {
        global $pdo;

        $sql = "SELECT * FROM acc_programa WHERE id_programas NOT IN (SELECT id_programas FROM acc_programa_x_rol r where r.id_rol = :id_rol)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_rol', $id_rol);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProgramasAsignados($id_rol) {
        global $pdo;

        $sql = "SELECT p.* FROM acc_programa p JOIN acc_programa_x_rol pr ON p.id_programas = pr.id_programas WHERE pr.id_rol = :id_rol";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_rol', $id_rol);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // No es necesario incluir la vista aquí
}
?>
