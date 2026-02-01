<?php
require_once(__DIR__ . '/conexion.php');

$query = "SHOW DATABASES";
$resultado = $conexion->query($query);

if ($resultado) {
    while ($fila = $resultado->fetch_array()) {
        $db_actual = ($_POST['base_datos'] ?? '') ?: ($_SESSION['base_datos'] ?? '');
        $selected = ($db_actual == $fila[0]) ? 'selected' : '';
        echo "<option value='" . htmlspecialchars($fila[0]) . "' $selected>" . 
             htmlspecialchars($fila[0]) . "</option>";
    }
} else {
    $error = $conexion->connect_error ?: ($conexion->error ?: "Error de conexión. Verifique .env");
    echo "<option value=''>Error: " . htmlspecialchars($error) . "</option>";
}
?>