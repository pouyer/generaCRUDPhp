<?php
session_start();

if (isset($_POST['ruta'])) {
    $_SESSION['ruta'] = $_POST['ruta'];
}
if (isset($_POST['nombre_archivo'])) {
    $_SESSION['nombre_archivo'] = $_POST['nombre_archivo'];
}
if (isset($_POST['base_datos'])) {
    $_SESSION['base_datos'] = $_POST['base_datos'];
}
if (isset($_POST['nombre_proyecto'])) {
    $_SESSION['nombre_proyecto'] = $_POST['nombre_proyecto'];
}
?> 