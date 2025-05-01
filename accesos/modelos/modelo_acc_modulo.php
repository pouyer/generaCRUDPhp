<?php
require_once '../../<conexion.php>';

class ModeloAcc_modulo {
    private $conexion;
    private $llavePrimaria = 'id_modulo';

    private $es_vista = false;

    public function __construct() {
        global $conexion;
        $this->conexion = $conexion;
    }

    public function contarRegistros() {
        $query = "SELECT COUNT(*) as total FROM acc_modulo";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : 0;
    }

    public function contarRegistrosPorBusqueda($termino) {
        $query = "SELECT COUNT(*) as total FROM v_acc_modulo WHERE ";
        $camposBusqueda = [];
        $camposBusqueda[] = "`id_modulo`";
        $camposBusqueda[] = "`nombre_modulo`";
        $camposBusqueda[] = "`icono`";
        $camposBusqueda[] = "`orden`";
        $camposBusqueda[] = "`nombre_estado`";
        $query .= "CONCAT_WS(' ', " . implode(', ', $camposBusqueda) . ") LIKE ?";
        $stmt = $this->conexion->prepare($query);
        $termino = "%" . $termino . "%";
        $stmt->bind_param('s', $termino);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : false;
    }
    public function obtenerTodos($registrosPorPagina, $offset) {
        $query = "SELECT * FROM v_acc_modulo";
        $query .= " LIMIT ? OFFSET ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $registrosPorPagina, $offset);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : false;
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM acc_modulo WHERE $this->llavePrimaria = ?";
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

        if (!empty($datos['nombre_modulo'])) {
          if (isset($datos['nombre_modulo'])) {
            $campos[] = '`nombre_modulo`';
            $valores[] = '?';
            $params[] = $datos['nombre_modulo'];
            $tipos .= 's';
           }
        }
        if (!empty($datos['icono'])) {
          if (isset($datos['icono'])) {
            $campos[] = '`icono`';
            $valores[] = '?';
            $params[] = $datos['icono'];
            $tipos .= 's';
           }
        }
        if (!empty($datos['orden'])) {
          if (isset($datos['orden'])) {
            $campos[] = '`orden`';
            $valores[] = '?';
            $params[] = $datos['orden'];
            $tipos .= 'i';
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
        if (!empty($datos['fecha_actualiza'])) {
          if (isset($datos['fecha_actualiza'])) {
            $campos[] = '`fecha_actualiza`';
            $valores[] = '?';
            $params[] = $datos['fecha_actualiza'];
            $tipos .= 's';
           }
        }

        $query = "INSERT INTO acc_modulo (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $valores) . ")";
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

        if (!empty($datos['nombre_modulo'])) {
            if (isset($datos['nombre_modulo'])) {
            $actualizaciones[] = "`nombre_modulo` = ?";
            $params[] = $datos['nombre_modulo'];
            $tipos .= 's';
        }
        }
        if (!empty($datos['icono'])) {
            if (isset($datos['icono'])) {
            $actualizaciones[] = "`icono` = ?";
            $params[] = $datos['icono'];
            $tipos .= 's';
        }
        }
        if (!empty($datos['orden'])) {
            if (isset($datos['orden'])) {
            $actualizaciones[] = "`orden` = ?";
            $params[] = $datos['orden'];
            $tipos .= 'i';
        }
        }
        if (!empty($datos['estado'])) {
            if (isset($datos['estado'])) {
            $actualizaciones[] = "`estado` = ?";
            $params[] = $datos['estado'];
            $tipos .= 's';
        }
        }
        if (!empty($datos['fecha_actualiza'])) {
            if (isset($datos['fecha_actualiza'])) {
            $actualizaciones[] = "`fecha_actualiza` = ?";
            $params[] = $datos['fecha_actualiza'];
            $tipos .= 's';
        }
        }

        $params[] = $id;
        $tipos .= $tipos_pk;
        $query = "UPDATE acc_modulo SET " . implode(', ', $actualizaciones) . " WHERE $this->llavePrimaria = ?";
        $stmt = $this->conexion->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($tipos, ...$params);
        }
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM acc_modulo WHERE $this->llavePrimaria = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    public function buscar($termino, $registrosPorPagina, $offset) {
        $query = "SELECT * FROM v_acc_modulo WHERE ";
        $camposBusqueda = [];
        $camposBusqueda[] = "`id_modulo`";
        $camposBusqueda[] = "`nombre_modulo`";
        $camposBusqueda[] = "`icono`";
        $camposBusqueda[] = "`orden`";
        $camposBusqueda[] = "`estado`";
        $query .= "CONCAT_WS(' ', " . implode(', ', $camposBusqueda) . ") LIKE ? LIMIT ? OFFSET ?";
        $stmt = $this->conexion->prepare($query);
        $termino = "%" . $termino . "%";
        $stmt->bind_param('sii', $termino, $registrosPorPagina, $offset);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : false;
    }
    public function exportarDatos($termino = '') {
        try {
            $query = "SELECT * FROM v_acc_modulo WHERE ";
            $camposBusqueda = [];
            $camposBusqueda[] = "`id_modulo`";
            $camposBusqueda[] = "`nombre_modulo`";
            $camposBusqueda[] = "`icono`";
            $camposBusqueda[] = "`orden`";
            $camposBusqueda[] = "`nombre_estado`";
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
        $sql = "SELECT estado, nombre_estado FROM acc_estado where tabla = 'acc_modulo' and visible = 1 order by orden"; // Asegúrate de que los nombres de las tablas y columnas sean correctos
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