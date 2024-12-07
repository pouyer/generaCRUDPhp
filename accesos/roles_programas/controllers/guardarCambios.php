<?php
require_once '../config.php'; // Asegúrate de incluir tu archivo de configuración

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_rol = $_POST['id_rol'];
    $programas = $_POST['programas'];

    // Depuración: Verifica que los datos se reciban correctamente
    error_log("ID Rol: " . $id_rol);
    error_log("Programas: " . json_encode($programas));

    // Primero, eliminar las relaciones existentes para el rol
    $sqlDelete = "DELETE FROM acc_programa_x_rol WHERE id_rol = :id_rol";
    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->bindParam(':id_rol', $id_rol);
    $stmtDelete->execute();

    // Luego, insertar las nuevas relaciones
    $sqlInsert = "INSERT INTO acc_programa_x_rol (id_programas, id_rol) VALUES (:id_programa, :id_rol)";
    $stmtInsert = $pdo->prepare($sqlInsert);

    foreach ($programas as $id_programa) {
        $stmtInsert->bindParam(':id_programa', $id_programa);
        $stmtInsert->bindParam(':id_rol', $id_rol);
        $stmtInsert->execute();
    }

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?> 