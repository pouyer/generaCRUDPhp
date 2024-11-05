<?php
session_start();

if(isset($_POST['ruta'])) {
    $_SESSION['ruta'] = $_POST['ruta'];
}

if(isset($_POST['nombre_archivo'])) {
    $_SESSION['nombre_archivo'] = $_POST['nombre_archivo'];
}
?> 