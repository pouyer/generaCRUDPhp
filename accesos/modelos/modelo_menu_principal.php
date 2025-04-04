<?php
require_once '../../<conexion.php>'; // Incluir el archivo de conexión

class ModeloMenu {
    private $conexion;

    public function __construct() {
        global $conexion; // Hacer la variable de conexión global
        $this->conexion = $conexion;
    }

    public function obtenerModulos($usuario = 0) {
        // Si no se proporciona un usuario, se usará 0 por defecto
        $sql = "SELECT DISTINCT modulo, icono_modulo FROM v_acc_menu WHERE ? IN (id_usuario, 0)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $usuario); // Vincular el parámetro como entero
        $stmt->execute();
        $resultado = $stmt->get_result();
        $modulos = [];

        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $modulos[] = $fila;
            }
        }
        $stmt->close();
        return $modulos;
    }

    public function obtenerMenusPorModulo($modulo, $usuario = 0) {
        $sql = "SELECT nombre_menu, ruta_programa, nombre_programaPHP, icono_programa FROM v_acc_menu WHERE modulo = ? AND ? IN (id_usuario, 0)";
        // Si no se proporciona un usuario, se usará 0 por defecto
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("si", $modulo, $usuario);
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