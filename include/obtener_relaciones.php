<?php
require_once(__DIR__ . '/funciones_utilidades.php');
require_once(__DIR__ . '/conexion.php');
header('Content-Type: application/json');

if (!isset($_POST['base_datos']) || !isset($_POST['tabla'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros']);
    exit;
}

$db = $_POST['base_datos'];
$tabla = $_POST['tabla'];

try {
    if (!$conexion->select_db($db)) {
        throw new Exception("No se pudo seleccionar la base de datos");
    }

    // Consultar Foreign Keys
    $sql = "SELECT 
                k.COLUMN_NAME, 
                k.REFERENCED_TABLE_NAME, 
                k.REFERENCED_COLUMN_NAME,
                c.IS_NULLABLE
            FROM 
                information_schema.KEY_COLUMN_USAGE k
            JOIN 
                information_schema.COLUMNS c ON k.TABLE_SCHEMA = c.TABLE_SCHEMA 
                AND k.TABLE_NAME = c.TABLE_NAME 
                AND k.COLUMN_NAME = c.COLUMN_NAME
            WHERE 
                k.TABLE_SCHEMA = ? 
                AND k.TABLE_NAME = ? 
                AND k.REFERENCED_TABLE_NAME IS NOT NULL";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('ss', $db, $tabla);
    $stmt->execute();
    $res = $stmt->get_result();

    $relaciones = [];
    while ($row = $res->fetch_assoc()) {
        $tablaPadre = $row['REFERENCED_TABLE_NAME'];
        
        // Obtener columnas de la tabla padre para que el usuario elija cuál mostrar
        $colRes = $conexion->query("SHOW COLUMNS FROM `$tablaPadre` FROM `$db` ");
        $columnasPadre = [];
        if ($colRes) {
            while ($col = $colRes->fetch_assoc()) {
                $columnasPadre[] = $col['Field'];
            }
        }

        $relaciones[] = [
            'campo_local' => $row['COLUMN_NAME'],
            'tabla_padre' => $tablaPadre,
            'campo_padre' => $row['REFERENCED_COLUMN_NAME'],
            'es_nullable' => ($row['IS_NULLABLE'] === 'YES'),
            'columnas_padre' => $columnasPadre
        ];
    }

    // Obtener columnas de la tabla actual para configuración de listado y exportación
    $colResActual = $conexion->query("SHOW COLUMNS FROM `$tabla` FROM `$db` ");
    $columnasTabla = [];
    if ($colResActual) {
        while ($col = $colResActual->fetch_assoc()) {
            $columnasTabla[] = $col['Field'];
        }
    }

    echo json_encode([
        'success' => true,
        'relaciones' => $relaciones,
        'columnas_tabla' => $columnasTabla
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
