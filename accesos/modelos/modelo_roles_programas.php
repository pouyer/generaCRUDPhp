<?php
// Buscar conexion.php de forma robusta
$rutaConexion = '';
$directorios = ['../../conexion.php', '../conexion.php', './conexion.php'];
foreach ($directorios as $dir) {
    if (file_exists($dir)) {
        $rutaConexion = $dir;
        break;
    }
}
if ($rutaConexion) {
    require_once $rutaConexion;
}

class ModeloRolesProgramas {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerRoles() {
        $sql = "SELECT id_rol, nombre_rol, estado FROM acc_rol ORDER BY nombre_rol ASC";
        $resultado = $this->conexion->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerProgramasNoAsignados($id_rol) {
        if (!$id_rol) return [];
        $sql = "SELECT id_programas, nombre_menu, icono FROM acc_programa WHERE id_programas NOT IN 
                (SELECT id_programas FROM acc_programa_x_rol r WHERE r.id_rol = ?) AND estado = 'A' ORDER BY nombre_menu ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('i', $id_rol);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerProgramasAsignados($id_rol) {
        if (!$id_rol) return [];
        $sql = "SELECT p.id_programas, p.nombre_menu, p.icono, 
                pr.permiso_insertar, pr.permiso_actualizar, pr.permiso_eliminar, pr.permiso_exportar 
                FROM acc_programa p 
                JOIN acc_programa_x_rol pr ON p.id_programas = pr.id_programas 
                WHERE pr.id_rol = ? ORDER BY p.nombre_menu ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('i', $id_rol);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function guardarCambios($id_rol, $programas) {
        if (!$id_rol) return ['status' => 'error', 'message' => 'Rol no especificado'];
        
        $this->conexion->begin_transaction();
        try {
            $sqlDelete = "DELETE FROM acc_programa_x_rol WHERE id_rol = ?";
            $stmtDelete = $this->conexion->prepare($sqlDelete);
            $stmtDelete->bind_param('i', $id_rol);
            $stmtDelete->execute();

            if (!empty($programas)) {
                $sqlInsert = "INSERT INTO acc_programa_x_rol (id_programas, id_rol, permiso_insertar, permiso_actualizar, permiso_eliminar, permiso_exportar) VALUES (?, ?, ?, ?, ?, ?)";
                $stmtInsert = $this->conexion->prepare($sqlInsert);
                foreach ($programas as $prog) {
                    // Si $prog es un array (viene con permisos)
                    if (is_array($prog)) {
                        $id_prog = $prog['id'];
                        $ins = $prog['ins'] ?? 1;
                        $upd = $prog['upd'] ?? 1;
                        $del = $prog['del'] ?? 1;
                        $exp = $prog['exp'] ?? 1;
                    } else {
                        // Si solo viene el ID (compatibilidad con lógica vieja)
                        $id_prog = $prog;
                        $ins = $upd = $del = $exp = 1;
                    }
                    $stmtInsert->bind_param('iiiiii', $id_prog, $id_rol, $ins, $upd, $del, $exp);
                    $stmtInsert->execute();
                }
            }
            $this->conexion->commit();
            return ['status' => 'success'];
        } catch (Exception $e) {
            $this->conexion->rollback();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
?>