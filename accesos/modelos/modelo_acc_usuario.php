<?php
require_once '../../<conexion.php>';

class ModeloAcc_usuario {
    private $conexion;
    private $llavePrimaria = 'id_usuario';

    private $es_vista = false;

    public function __construct() {
        global $conexion;
        $this->conexion = $conexion;
    }

    public function contarRegistros() {
        $query = "SELECT COUNT(*) as total FROM acc_usuario";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : 0;
    }

    public function contarRegistrosPorBusqueda($termino) {
        $query = "SELECT COUNT(*) as total FROM acc_usuario WHERE ";
        $camposBusqueda = [];
        $camposBusqueda[] = "`id_usuario`";
        $camposBusqueda[] = "`username`";
        $camposBusqueda[] = "`fullname`";
        $camposBusqueda[] = "`correo`";
        $camposBusqueda[] = "`password`";
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
    public function obtenerTodos($registrosPorPagina, $offset) {
        $query = "SELECT * FROM acc_usuario";
        $query .= " LIMIT ? OFFSET ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $registrosPorPagina, $offset);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : false;
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM acc_usuario WHERE $this->llavePrimaria = ?";
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

        if (empty($datos['username'])) {
            throw new Exception('El campo username es requerido.');
        } elseif (isset($datos['username'])) {
            $campos[] = '`username`';
            $valores[] = '?';
            $params[] = $datos['username'];
            $tipos .= 's';
        }
        if (empty($datos['fullname'])) {
            throw new Exception('El campo fullname es requerido.');
        } elseif (isset($datos['fullname'])) {
            $campos[] = '`fullname`';
            $valores[] = '?';
            $params[] = $datos['fullname'];
            $tipos .= 's';
        }
        if (!empty($datos['correo'])) {
          if (isset($datos['correo'])) {
            $campos[] = '`correo`';
            $valores[] = '?';
            $params[] = $datos['correo'];
            $tipos .= 's';
           }
        }
        if (!empty($datos['password'])) {
          if (isset($datos['password'])) {
            $campos[] = '`password`';
            $valores[] = '?';
            $params[] = $datos['password'];
            $tipos .= 's';
           }
        }
        if (empty($datos['estado'])) {
            throw new Exception('El campo estado es requerido.');
        } elseif (isset($datos['estado'])) {
            $campos[] = '`estado`';
            $valores[] = '?';
            $params[] = $datos['estado'];
            $tipos .= 's';
        }

        $query = "INSERT INTO acc_usuario (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $valores) . ")";
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

        if (empty($datos['username'])) {
            throw new Exception('El campo username es requerido.');
        } elseif (isset($datos['username'])) {
            $actualizaciones[] = "`username` = ?";
            $params[] = $datos['username'];
            $tipos .= 's';
        }
        if (empty($datos['fullname'])) {
            throw new Exception('El campo fullname es requerido.');
        } elseif (isset($datos['fullname'])) {
            $actualizaciones[] = "`fullname` = ?";
            $params[] = $datos['fullname'];
            $tipos .= 's';
        }
        if (!empty($datos['correo'])) {
            if (isset($datos['correo'])) {
            $actualizaciones[] = "`correo` = ?";
            $params[] = $datos['correo'];
            $tipos .= 's';
        }
        }
        if (!empty($datos['password'])) {
            if (isset($datos['password'])) {
            $actualizaciones[] = "`password` = ?";
            $params[] = $datos['password'];
            $tipos .= 's';
        }
        }
        if (empty($datos['estado'])) {
            throw new Exception('El campo estado es requerido.');
        } elseif (isset($datos['estado'])) {
            $actualizaciones[] = "`estado` = ?";
            $params[] = $datos['estado'];
            $tipos .= 's';
        }

        $params[] = $id;
        $tipos .= $tipos_pk;
        $query = "UPDATE acc_usuario SET " . implode(', ', $actualizaciones) . " WHERE $this->llavePrimaria = ?";
        $stmt = $this->conexion->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($tipos, ...$params);
        }
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM acc_usuario WHERE $this->llavePrimaria = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    public function buscar($termino, $registrosPorPagina, $offset) {
        $query = "SELECT * FROM acc_usuario WHERE ";
        $camposBusqueda = [];
        $camposBusqueda[] = "`id_usuario`";
        $camposBusqueda[] = "`username`";
        $camposBusqueda[] = "`fullname`";
        $camposBusqueda[] = "`correo`";
        $camposBusqueda[] = "`password`";
        $camposBusqueda[] = "`estado`";
        $camposBusqueda[] = "`fecha_creacion`";
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
            $query = "SELECT * FROM acc_usuario WHERE ";
            $camposBusqueda = [];
            $camposBusqueda[] = "`id_usuario`";
            $camposBusqueda[] = "`username`";
            $camposBusqueda[] = "`fullname`";
            $camposBusqueda[] = "`correo`";
            $camposBusqueda[] = "`password`";
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


    public function obtenerRolesPorUsuario($id_usuario) {
        $query = "SELECT ru.id_usuario, ru.id_rol, r.nombre_rol FROM acc_rol_x_usuario ru join acc_rol r on(r.id_rol = ru.id_rol) WHERE ru.id_usuario = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : false;
    }

    public function obtenerTodosRoles() {
        $query = "SELECT * FROM acc_rol";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : false;
    }
    
    public function eliminarRol($id_usuario, $id_rol) {
        $query = "DELETE FROM acc_rol_x_usuario WHERE id_usuario = ? AND id_rol = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $id_usuario, $id_rol);
        return $stmt->execute();
    }

    public function agregarRol($id_usuario, $id_rol) {
        $query = "INSERT INTO acc_rol_x_usuario (id_usuario, id_rol) VALUES (?, ?)";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $id_usuario, $id_rol);
        return $stmt->execute();
    }

}
?>