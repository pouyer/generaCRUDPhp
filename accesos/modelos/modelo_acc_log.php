<?php
// Buscar el archivo de conexión de forma robusta
$conexion_encontrada = false;
$posibles_rutas = [
    __DIR__ . '/../../<conexion.php>',
    __DIR__ . '/../<conexion.php>',
    dirname(dirname(__DIR__)) . '/<conexion.php>'
];

foreach ($posibles_rutas as $ruta) {
    if (file_exists($ruta)) {
        require_once $ruta;
        $conexion_encontrada = true;
        break;
    }
}

if (!$conexion_encontrada) {
    // Si no se encuentra, intentamos con el nombre por defecto reemplazado por el generador
    // Esto es un fallback por si los anteriores fallan en contextos inesperados
    @include_once '../../<conexion.php>';
}

class ModeloAcc_log {
    private $conexion;

    public function __construct() {
        global $conexion;
        $this->conexion = $conexion;
    }

    public function registrar($id_usuario, $accion, $tabla = null, $detalles = null) {
        if (!$this->conexion) return false;
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $query = "INSERT INTO acc_log_accion (id_usuario, accion, tabla, detalles, ip) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($query);
        if (!$stmt) return false;
        
        $stmt->bind_param("issss", $id_usuario, $accion, $tabla, $detalles, $ip);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function contarRegistros() {
        $query = "SELECT COUNT(*) as total FROM acc_log_accion";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : 0;
    }

    public function obtenerTodos($registrosPorPagina, $offset) {
        $query = "SELECT l.*, u.username FROM acc_log_accion l 
                  LEFT JOIN acc_usuario u ON l.id_usuario = u.id_usuario 
                  ORDER BY l.fecha DESC LIMIT ? OFFSET ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $registrosPorPagina, $offset);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : false;
    }

    public function buscar($termino, $registrosPorPagina, $offset) {
        $termino = "%$termino%";
        $query = "SELECT l.*, u.username FROM acc_log_accion l 
                  LEFT JOIN acc_usuario u ON l.id_usuario = u.id_usuario 
                  WHERE l.accion LIKE ? OR l.tabla LIKE ? OR l.detalles LIKE ? OR u.username LIKE ?
                  ORDER BY l.fecha DESC LIMIT ? OFFSET ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ssssii', $termino, $termino, $termino, $termino, $registrosPorPagina, $offset);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : false;
    }

    public function contarRegistrosPorBusqueda($termino) {
        $termino = "%$termino%";
        $query = "SELECT COUNT(*) as total FROM acc_log_accion l 
                  LEFT JOIN acc_usuario u ON l.id_usuario = u.id_usuario 
                  WHERE l.accion LIKE ? OR l.tabla LIKE ? OR l.detalles LIKE ? OR u.username LIKE ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ssss', $termino, $termino, $termino, $termino);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc()['total'] : 0;
    }

    public function exportarDatos($termino = '') {
        $query = "SELECT l.id_log, u.username, l.accion, l.tabla, l.detalles, l.ip, l.fecha 
                  FROM acc_log_accion l 
                  LEFT JOIN acc_usuario u ON l.id_usuario = u.id_usuario ";
        
        if (!empty($termino)) {
            $termino = "%$termino%";
            $query .= " WHERE l.accion LIKE ? OR l.tabla LIKE ? OR l.detalles LIKE ? OR u.username LIKE ?";
        }
        $query .= " ORDER BY l.fecha DESC";

        $stmt = $this->conexion->prepare($query);
        if (!empty($termino)) {
            $stmt->bind_param('ssss', $termino, $termino, $termino, $termino);
        }
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
}
?>
