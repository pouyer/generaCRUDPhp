<?php
session_start();

if (isset($_POST['ruta'])) {
    $_SESSION['ruta'] = $_POST['ruta'];
}
if (isset($_POST['nombre_archivo'])) {
    $_SESSION['nombre_archivo'] = $_POST['nombre_archivo'];
} else {
    $_SESSION['nombre_archivo'] = 'conexion.php';
}
if (isset($_POST['base_datos'])) {
    $_SESSION['base_datos'] = $_POST['base_datos'];
}
if (isset($_POST['nombre_proyecto'])) {
    $_SESSION['nombre_proyecto'] = $_POST['nombre_proyecto'];
}
if (isset($_POST['admin_email'])) {
    $_SESSION['admin_email'] = $_POST['admin_email'];
}
if (isset($_POST['config_tablas'])) {
    $_SESSION['config_tablas'] = $_POST['config_tablas'];
}
?>