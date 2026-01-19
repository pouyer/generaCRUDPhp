<?php
require_once '../../conexion.php';

class ModeloAcc_rol {
    private $conexion;
    private $llavePrimaria = 'id_rol';

    private $es_vista = false;

    public function __construct() {
        global $conexion;
        $this->conexion = $conexion;
    }

    public function contarRegistros() {
        $query = "SELECT COUNT(*) as total FROM acc_rol";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : 0;
    }

    public function contarRegistrosPorBusqueda($termino) {
        $query = "SELECT COUNT(*) as total FROM v_acc_rol WHERE ";
        $camposBusqueda = [];
        $camposBusqueda[] = "`id_rol`";
        $camposBusqueda[] = "`nombre_rol`";
        $camposBusqueda[] = "`estado`";
        $camposBusqueda[] = "`fecha_creacion`";
        $query .= "CONCAT_WS(' ', " . implode(', ', $camposBusqueda) . ") LIKE ?";
        $stmt = $this->conexion->prepare($query);
        $termino = "%" . $termino . "%";
        $stmt->bind_param('s', $termino);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : false;
    }
    public function obtenerTodos($registrosPorPagina, $offset, $orderBy = null, $orderDir = 'DESC') {
        $columnasPermitidas = ['id_rol', 'nombre_rol', 'estado', 'fecha_creacion'];
        $orderBy = in_array($orderBy, $columnasPermitidas) ? $orderBy : 'id_rol';
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $query = "SELECT * FROM v_acc_rol ORDER BY $orderBy $orderDir LIMIT ? OFFSET ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $registrosPorPagina, $offset);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : false;
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM acc_rol WHERE $this->llavePrimaria = ?";
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

        if (!empty($datos['nombre_rol'])) {
          if (isset($datos['nombre_rol'])) {
            $campos[] = '`nombre_rol`';
            $valores[] = '?';
            $params[] = $datos['nombre_rol'];
            $tipos .= 's';
           }
        }
        if (!empty($datos['estado'])) {
          if (isset($datos['estado'])) {
            $campos[] = '`estado`';
            $valores[] = '?';
            $params[] = $datos['estado'];
            $tipos .= 's';
           }
        }
        
        // Datos de auditoría
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        if ($usuario_id) {
            $campos[] = '`usuario_id_inserto`';
            $valores[] = '?';
            $params[] = $usuario_id;
            $tipos .= 'i';
        }

        $query = "INSERT INTO acc_rol (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $valores) . ")";
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

        if (array_key_exists('nombre_rol', $datos)) {
            if ($datos['nombre_rol'] === '') throw new Exception('El nombre del rol es requerido.');
            $actualizaciones[] = "`nombre_rol` = ?";
            $params[] = $datos['nombre_rol'];
            $tipos .= 's';
        }

        if (array_key_exists('estado', $datos)) {
            $actualizaciones[] = "`estado` = ?";
            $params[] = $datos['estado'];
            $tipos .= 's';
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
        $query = "UPDATE acc_rol SET " . implode(', ', $actualizaciones) . " WHERE $this->llavePrimaria = ?";
        $stmt = $this->conexion->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($tipos, ...$params);
        }
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM acc_rol WHERE $this->llavePrimaria = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    public function buscar($termino, $registrosPorPagina, $offset, $orderBy = null, $orderDir = 'DESC') {
        $columnasPermitidas = ['id_rol', 'nombre_rol', 'estado', 'fecha_creacion'];
        $orderBy = in_array($orderBy, $columnasPermitidas) ? $orderBy : 'id_rol';
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $query = "SELECT * FROM v_acc_rol WHERE ";
        $camposBusqueda = [];
        $camposBusqueda[] = "`id_rol`";
        $camposBusqueda[] = "`nombre_rol`";
        $camposBusqueda[] = "`estado`";
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
            $query = "SELECT * FROM v_acc_rol WHERE ";
            $camposBusqueda = [];
            $camposBusqueda[] = "`id_rol`";
            $camposBusqueda[] = "`nombre_rol`";
            $camposBusqueda[] = "`estado`";
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

    public function obtenerEstados() {
        $sql = "SELECT estado, nombre_estado FROM acc_estado where tabla = 'acc_rol' and visible = 1 order by orden"; // Asegúrate de que los nombres de las tablas y columnas sean correctos
        $resultado = $this->conexion->query($sql);
        $estados = [];

        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $estados[] = $fila;
            }
        }
        return $estados;
    } 

}
?>