<?php
require_once '../../<conexion.php>';

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
        $camposBusqueda[] = "`ruta`";
        $camposBusqueda[] = "`nombre_archivo`";
        $camposBusqueda[] = "`orden`";
        $camposBusqueda[] = "`nombre_estado`";
        $camposBusqueda[] = "`nombre_modulo`";
        $camposBusqueda[] = "`fecha_creacion`";
        $query .= "CONCAT_WS(' ', " . implode(', ', $camposBusqueda) . ") LIKE ?";
        $stmt = $this->conexion->prepare($query);
        $termino = "%" . $termino . "%";
        $stmt->bind_param('s', $termino);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : false;
    }
    public function obtenerTodos($registrosPorPagina, $offset) {
        $query = "SELECT id_programas, nombre_menu, ruta, nombre_archivo, id_modulo, orden, estado, nombre_estado, nombre_modulo, fecha_creacion 
        FROM `v_acc_programa`";
        $query .= " LIMIT ? OFFSET ?";
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

        if (!empty($datos['nombre_menu'])) {
          if (isset($datos['nombre_menu'])) {
            $campos[] = '`nombre_menu`';
            $valores[] = '?';
            $params[] = $datos['nombre_menu'];
            $tipos .= 's';
           }
        }
        if (!empty($datos['ruta'])) {
          if (isset($datos['ruta'])) {
            $campos[] = '`ruta`';
            $valores[] = '?';
            $params[] = $datos['ruta'];
            $tipos .= 's';
           }
        }
        if (!empty($datos['nombre_archivo'])) {
          if (isset($datos['nombre_archivo'])) {
            $campos[] = '`nombre_archivo`';
            $valores[] = '?';
            $params[] = $datos['nombre_archivo'];
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
        if (!empty($datos['id_modulo'])) {
          if (isset($datos['id_modulo'])) {
            $campos[] = '`id_modulo`';
            $valores[] = '?';
            $params[] = $datos['id_modulo'];
            $tipos .= 'i';
           }
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

        if (!empty($datos['nombre_menu'])) {
            if (isset($datos['nombre_menu'])) {
            $actualizaciones[] = "`nombre_menu` = ?";
            $params[] = $datos['nombre_menu'];
            $tipos .= 's';
        }
        }
        if (!empty($datos['ruta'])) {
            if (isset($datos['ruta'])) {
            $actualizaciones[] = "`ruta` = ?";
            $params[] = $datos['ruta'];
            $tipos .= 's';
        }
        }
        if (!empty($datos['nombre_archivo'])) {
            if (isset($datos['nombre_archivo'])) {
            $actualizaciones[] = "`nombre_archivo` = ?";
            $params[] = $datos['nombre_archivo'];
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
        if (!empty($datos['id_modulo'])) {
            if (isset($datos['id_modulo'])) {
            $actualizaciones[] = "`id_modulo` = ?";
            $params[] = $datos['id_modulo'];
            $tipos .= 'i';
        }
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
    public function buscar($termino, $registrosPorPagina, $offset) {
        $query = "SELECT id_programas, nombre_menu, ruta, nombre_archivo, id_modulo, orden, estado, nombre_estado, nombre_modulo, fecha_creacion 
        FROM `v_acc_programa` `p` WHERE ";
        $camposBusqueda = [];
        $camposBusqueda[] = "`p`.`id_programas`";
        $camposBusqueda[] = "`p`.`nombre_menu`";
        $camposBusqueda[] = "`p`.`ruta`";
        $camposBusqueda[] = "`p`.`nombre_archivo`";
        $camposBusqueda[] = "`p`.`orden`";
        $camposBusqueda[] = "`p`.`nombre_estado`";
        $camposBusqueda[] = "`p`.`nombre_modulo`";
        $camposBusqueda[] = "`p`.`fecha_creacion`";
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
            $query = "SELECT id_programas, nombre_menu, ruta, nombre_archivo, id_modulo, orden, estado, nombre_estado, nombre_modulo, fecha_creacion 
        FROM `v_acc_programa` `p` WHERE  ";
            $camposBusqueda = [];
            $camposBusqueda[] = "`p`.`id_programas`";
            $camposBusqueda[] = "`p`.`nombre_menu`";
            $camposBusqueda[] = "`p`.`ruta`";
            $camposBusqueda[] = "`p`.`nombre_archivo`";
            $camposBusqueda[] = "`p`.`orden`";
            $camposBusqueda[] = "`p`.`nombre_estado`";
            $camposBusqueda[] = "`p`.`nombre_modulo`";
            $camposBusqueda[] = "`p`.`fecha_creacion`";
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