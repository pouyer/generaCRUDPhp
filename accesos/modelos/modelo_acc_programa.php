<?php
require_once '../../conexion.php';

class ModeloAcc_programa {
    private $conexion;
    private $llavePrimaria = 'id_programas';

    private $es_vista = false;

    public function __construct() {
        global $conexion;
        $this->conexion = $conexion;
    }

    public function contarRegistros() {
        $query = "SELECT COUNT(*) as total FROM acc_programa";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : 0;
    }

    public function contarRegistrosPorBusqueda($termino) {
        $query = "SELECT COUNT(*) as total FROM v_acc_programa WHERE ";
        $camposBusqueda = [];
        $camposBusqueda[] = "`id_programas`";
        $camposBusqueda[] = "`nombre_menu`";
        $camposBusqueda[] = "`icono`";
        $camposBusqueda[] = "`ruta`";
        $camposBusqueda[] = "`nombre_archivo`";
        $camposBusqueda[] = "`orden`";
        $camposBusqueda[] = "`estado`";
        $camposBusqueda[] = "`id_modulo`";
        $query .= "CONCAT_WS(' ', " . implode(', ', $camposBusqueda) . ") LIKE ?";
        $stmt = $this->conexion->prepare($query);
        $termino = "%" . $termino . "%";
        $stmt->bind_param('s', $termino);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : false;
    }
    public function obtenerTodos($registrosPorPagina, $offset, $orderBy = null, $orderDir = 'ASC') {
        $columnasPermitidas = ['id_programas', 'nombre_menu', 'nombre_modulo', 'orden', 'estado', 'fecha_creacion'];
        $orderBy = in_array($orderBy, $columnasPermitidas) ? $orderBy : 'nombre_modulo, orden';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $query = "SELECT * FROM v_acc_programa ORDER BY $orderBy $orderDir LIMIT ? OFFSET ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $registrosPorPagina, $offset);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : false;
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM acc_programa WHERE $this->llavePrimaria = ?";
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

        if (array_key_exists('nombre_menu', $datos)) {
            $campos[] = '`nombre_menu`';
            $valores[] = '?';
            $params[] = $datos['nombre_menu'];
            $tipos .= 's';
        }
        if (array_key_exists('icono', $datos)) {
            $campos[] = '`icono`';
            $valores[] = '?';
            $params[] = $datos['icono'];
            $tipos .= 's';
        }
        if (array_key_exists('ruta', $datos)) {
            $campos[] = '`ruta`';
            $valores[] = '?';
            $params[] = $datos['ruta'];
            $tipos .= 's';
        }
        if (array_key_exists('nombre_archivo', $datos)) {
            $campos[] = '`nombre_archivo`';
            $valores[] = '?';
            $params[] = $datos['nombre_archivo'];
            $tipos .= 's';
        }
        if (array_key_exists('orden', $datos)) {
            $campos[] = '`orden`';
            $valores[] = '?';
            $params[] = ($datos['orden'] === '' || $datos['orden'] === null) ? null : (int)$datos['orden'];
            $tipos .= 'i';
        }
        if (array_key_exists('estado', $datos)) {
            $campos[] = '`estado`';
            $valores[] = '?';
            $params[] = $datos['estado'];
            $tipos .= 's';
        }
        if (array_key_exists('id_modulo', $datos)) {
            $campos[] = '`id_modulo`';
            $valores[] = '?';
            $params[] = ($datos['id_modulo'] === '' || $datos['id_modulo'] === null) ? null : (int)$datos['id_modulo'];
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

        $query = "INSERT INTO acc_programa (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $valores) . ")";
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

        if (array_key_exists('nombre_menu', $datos)) {
            $actualizaciones[] = "`nombre_menu` = ?";
            $params[] = $datos['nombre_menu'];
            $tipos .= 's';
        }
        if (array_key_exists('icono', $datos)) {
            $actualizaciones[] = "`icono` = ?";
            $params[] = $datos['icono'];
            $tipos .= 's';
        }
        if (array_key_exists('ruta', $datos)) {
            $actualizaciones[] = "`ruta` = ?";
            $params[] = $datos['ruta'];
            $tipos .= 's';
        }
        if (array_key_exists('nombre_archivo', $datos)) {
            $actualizaciones[] = "`nombre_archivo` = ?";
            $params[] = $datos['nombre_archivo'];
            $tipos .= 's';
        }
        if (array_key_exists('orden', $datos)) {
            $actualizaciones[] = "`orden` = ?";
            $params[] = ($datos['orden'] === '' || $datos['orden'] === null) ? null : (int)$datos['orden'];
            $tipos .= 'i';
        }
        if (array_key_exists('estado', $datos)) {
            $actualizaciones[] = "`estado` = ?";
            $params[] = $datos['estado'];
            $tipos .= 's';
        }
        if (array_key_exists('id_modulo', $datos)) {
            $actualizaciones[] = "`id_modulo` = ?";
            $params[] = ($datos['id_modulo'] === '' || $datos['id_modulo'] === null) ? null : (int)$datos['id_modulo'];
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
        $query = "UPDATE acc_programa SET " . implode(', ', $actualizaciones) . " WHERE $this->llavePrimaria = ?";
        $stmt = $this->conexion->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($tipos, ...$params);
        }
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM acc_programa WHERE $this->llavePrimaria = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    public function buscar($termino, $registrosPorPagina, $offset, $orderBy = null, $orderDir = 'ASC') {
        $columnasPermitidas = ['id_programas', 'nombre_menu', 'nombre_modulo', 'orden', 'estado', 'fecha_creacion'];
        $orderBy = in_array($orderBy, $columnasPermitidas) ? $orderBy : 'nombre_modulo, orden';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $query = "SELECT * FROM v_acc_programa WHERE ";
        $camposBusqueda = [];
        $camposBusqueda[] = "`id_programas`";
        $camposBusqueda[] = "`nombre_menu`";
        $camposBusqueda[] = "`icono`";
        $camposBusqueda[] = "`ruta`";
        $camposBusqueda[] = "`nombre_archivo`";
        $camposBusqueda[] = "`orden`";
        $camposBusqueda[] = "`estado`";
        $camposBusqueda[] = "`nombre_modulo`";
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
            $query = "SELECT id_programas id, nombre_modulo Modulo, nombre_menu menu, icono, ruta, nombre_archivo, orden, estado,  fecha_creacion FROM v_acc_programa WHERE ";
            $camposBusqueda = [];
            $camposBusqueda[] = "`id_programas`";
            $camposBusqueda[] = "`nombre_menu`";
            $camposBusqueda[] = "`icono`";
            $camposBusqueda[] = "`ruta`";
            $camposBusqueda[] = "`nombre_archivo`";
            $camposBusqueda[] = "`orden`";
            $camposBusqueda[] = "`estado`";
            $camposBusqueda[] = "`nombre_modulo`";
            $camposBusqueda[] = "`fecha_creacion`";
            $query .= "CONCAT_WS(' ', " . implode(', ', $camposBusqueda) . ") LIKE ?";
			$query .= " order by nombre_modulo, orden";
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
        $sql = "SELECT estado, nombre_estado FROM acc_estado where tabla = 'acc_programa' and visible = 1 order by orden"; // Asegúrate de que los nombres de las tablas y columnas sean correctos
        $resultado = $this->conexion->query($sql);
        $estados = [];

        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $estados[] = $fila;
            }
        }
        return $estados;
    }
	
	public function obtenerModulos() {
        $sql = "SELECT id_modulo, nombre_modulo FROM acc_modulo"; // Asegúrate de que los nombres de las tablas y columnas sean correctos
        $resultado = $this->conexion->query($sql);
        $modulos = [];

        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $modulos[] = $fila;
            }
        }
        return $modulos;
    }

}
?>