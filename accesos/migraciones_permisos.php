<?php
require_once 'conexion.php';

$sql = "ALTER TABLE acc_programa_x_rol 
        ADD COLUMN IF NOT EXISTS permiso_insertar TINYINT(1) DEFAULT 1,
        ADD COLUMN IF NOT EXISTS permiso_actualizar TINYINT(1) DEFAULT 1,
        ADD COLUMN IF NOT EXISTS permiso_eliminar TINYINT(1) DEFAULT 1,
        ADD COLUMN IF NOT EXISTS permiso_exportar TINYINT(1) DEFAULT 1";

if ($conexion->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Migración completada con éxito.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error en la migración: ' . $conexion->error]);
}
?>
