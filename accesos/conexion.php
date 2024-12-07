<?php
    $host = 'localhost';
    $usuario = 'u420920295_quickbite';
    $password = '';
    $database = 'u420920295_quickbite';
    $conexion = new mysqli($host, $usuario, $password, $database);

    if ($conexion->connect_error) {
        die('Error en la conexión (' . $conexion->connect_errno . '): ' . $conexion->connect_error);
    }

    $conexion->set_charset('utf8');
    //Comentar esta linea una ves probada la conexion;
    // echo 'Conexión exitosa';
?>