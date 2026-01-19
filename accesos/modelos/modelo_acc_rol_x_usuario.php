<?php
require_once '../../<conexion.php>';

class ModeloAcc_rol_x_usuario {
    private $conexion;
    private $llavePrimaria = 'id_rol';

    private $es_vista = false;

    public function __construct() {
        global $conexion;
        $this->conexion = $conexion;
    }

    public function contarRegistros() {
        $query = "SELECT COUNT(*) as total FROM acc_rol_x_usuario";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : 0;
    }

    public function contarRegistrosPorBusqueda($termino) {
        $query = "SELECT COUNT(*) as total FROM v_acc_rol_x_usuario WHERE ";
        $camposBusqueda = [];
        $camposBusqueda[] = "`id_usuario`";
        $camposBusqueda[] = "`nombre_usuario`";
        $camposBusqueda[] = "`id_rol`";
        $camposBusqueda[] = "`nombre_rol`";
        $camposBusqueda[] = "`fecha_creacion`";
        $query .= "CONCAT_WS(' ', " . implode(', ', $camposBusqueda) . ") LIKE ?";
        $stmt = $this->conexion->prepare($query);
        $termino = "%" . $termino . "%";
        $stmt->bind_param('s', $termino);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : false;
    }
    public function obtenerTodos($registrosPorPagina, $offset, $orderBy = null, $orderDir = 'ASC') {
        $columnasPermitidas = ['id_usuario', 'nombre_usuario', 'id_rol', 'nombre_rol', 'fecha_creacion'];
        $orderBy = in_array($orderBy, $columnasPermitidas) ? $orderBy : 'nombre_usuario, nombre_rol';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $query = "SELECT * FROM v_acc_rol_x_usuario ORDER BY $orderBy $orderDir LIMIT ? OFFSET ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $registrosPorPagina, $offset);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : false;
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM acc_rol_x_usuario WHERE $this->llavePrimaria = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc() : false;
    }

    public function crear($datos) {
        $campos = [];
        $valores = [];
        $tipos = '';
        $params = [];

        if (array_key_exists('id_rol', $datos)) {
            if ($datos['id_rol'] === '') throw new Exception('El campo id_rol es requerido.');
            $campos[] = '`id_rol`';
            $valores[] = '?';
            $params[] = $datos['id_rol'];
            $tipos .= 'i';
        }

        // Datos de auditoría
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        if ($usuario_id) {
            $campos[] = '`usuario_id_inserto`';
            $valores[] = '?';
            $params[] = $usuario_id;
            $tipos .= 'i';
        }

        $query = "INSERT INTO acc_rol_x_usuario (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $valores) . ")";
        $stmt = $this->conexion->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($tipos, ...$params);
        }
        return $stmt->execute();
    }

    public function actualizar($id, $datos) {
        error_log(print_r($datos, true)); // Verificar los datos recibidos
        $actualizaciones = [];
        $tipos = '';
        $tipos_pk = 'i'; // Para la llave primaria
        $params = [];

        if (!empty($datos['id_rol'])) {
            $actualizaciones[] = "`id_rol` = ?";
            $params[] = $datos['id_rol'];
            $tipos .= 'i';
        }

        // Datos de auditoría
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        if ($usuario_id) {
            $actualizaciones[] = "`usuario_id_actualizo` = ?";
            $params[] = $usuario_id;
            $tipos .= 'i';
        }

        $params[] = $id;
        $tipos .= $tipos_pk;
        $query = "UPDATE acc_rol_x_usuario SET " . implode(', ', $actualizaciones) . " WHERE $this->llavePrimaria = ?";
        $stmt = $this->conexion->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($tipos, ...$params);
        }
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM acc_rol_x_usuario WHERE $this->llavePrimaria = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    public function buscar($termino, $registrosPorPagina, $offset, $orderBy = null, $orderDir = 'ASC') {
        $columnasPermitidas = ['id_usuario', 'nombre_usuario', 'id_rol', 'nombre_rol', 'fecha_creacion'];
        $orderBy = in_array($orderBy, $columnasPermitidas) ? $orderBy : 'nombre_usuario, nombre_rol';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $query = "SELECT * FROM v_acc_rol_x_usuario WHERE ";
        $camposBusqueda = [];
        $camposBusqueda[] = "`id_usuario`";
        $camposBusqueda[] = "`nombre_usuario`";
        $camposBusqueda[] = "`id_rol`";
        $camposBusqueda[] = "`nombre_rol`";
        $camposBusqueda[] = "`fecha_creacion`";
        $query .= "CONCAT_WS(' ', " . implode(', ', $camposBusqueda) . ") LIKE ? ORDER BY $orderBy $orderDir LIMIT ? OFFSET ?";
        $stmt = $this->conexion->prepare($query);
        $termino = "%" . $termino . "%";
        $stmt->bind_param('sii', $termino, $registrosPorPagina, $offset);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : false;
    }
    public function exportarDatos($termino = '') {
        try {
            $query = "SELECT * FROM v_acc_rol_x_usuario WHERE ";
            $camposBusqueda = [];
            $camposBusqueda[] = "`id_usuario`";
            $camposBusqueda[] = "`nombre_usuario`";
            $camposBusqueda[] = "`id_rol`";
            $camposBusqueda[] = "`nombre_rol`";
            $camposBusqueda[] = "`fecha_creacion`";
            $query .= "CONCAT_WS(' ', " . implode(', ', $camposBusqueda) . ") LIKE ?";
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

}
?>