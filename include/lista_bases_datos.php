<?php
require_once('conexion.php');

$query = "SHOW DATABASES";
$resultado = $conexion->query($query);

if ($resultado) {
    while ($fila = $resultado->fetch_array()) {
        $selected = (isset($_POST['base_datos']) && $_POST['base_datos'] == $fila[0]) ? 'selected' : '';
        echo "<option value='" . htmlspecialchars($fila[0]) . "' $selected>" . 
             htmlspecialchars($fila[0]) . "</option>";
    }
} else {
    $error = $conexion->error ? $conexion->error : "Error desconocido o conexión no establecida.";
    echo "<option value=''>Error: " . htmlspecialchars($error) . "</option>";
}
?>