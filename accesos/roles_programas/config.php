<?php
$host = 'localhost'; // Cambia esto si es necesario
$db = 'accesospython'; // Cambia esto por el nombre de tu base de datos
$user = 'root'; // Cambia esto por tu usuario de base de datos
$pass = ''; // Cambia esto por tu contraseña de base de datos

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?> 