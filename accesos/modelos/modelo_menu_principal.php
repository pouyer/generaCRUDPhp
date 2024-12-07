<?php
require_once '../../<conexion.php>'; // Incluir el archivo de conexión

class ModeloMenu {
    private $conexion;

    public function __construct() {
        global $conexion; // Hacer la variable de conexión global
        $this->conexion = $conexion;
    }

    public function obtenerModulos() {
        $sql = "SELECT DISTINCT modulo FROM v_acc_menu";
        $resultado = $this->conexion->query($sql);
        $modulos = [];

        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $modulos[] = $fila;
            }
        }
        return $modulos;
    }

    public function obtenerMenusPorModulo($modulo) {
        $sql = "SELECT nombre_menu, ruta_programa, nombre_programaPHP FROM v_acc_menu WHERE modulo = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $modulo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $menus = [];

        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $menus[] = $fila;
            }
        }
        $stmt->close();
        return $menus;
    }
} 