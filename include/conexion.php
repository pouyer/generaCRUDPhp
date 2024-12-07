<?php

    // Verificar si se seleccionó una base de datos
    // Configuración de la conexión
   
 
  /*
   $servidor = 'localhost';
   $usuario = 'root';
   $password = '';
   $puerto = 3306;
*/

   $servidor = 'srv691.hstgr.io';
   $usuario = 'u420920295_QuickBite';
   $password = 'QuickBite1';
   $puerto = 3306;

/*	$servidor = 'serverltda.com';
    $usuario = 'remoto';
    $password = 'C@rlos123';
    $puerto = 17032; // Cambia este valor al puerto que necesites
*/	
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $dbname = $_POST['base_datos'];
        $conexion = new mysqli($servidor, $usuario, $password, $dbname, $puerto);
    } else {
        // Manejar el caso cuando no se ha enviado el formulario
        $conexion = new mysqli($servidor, $usuario, $password, null, $puerto);
    }
    
    
    // Verificar si la conexión fue exitosa
    if ($conexion->connect_errno) {
        die("Error de conexión: " . $conexion->connect_error);
    }
?>