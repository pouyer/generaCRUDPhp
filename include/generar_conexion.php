<?php
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $host = $_POST['host'];
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    $ruta = $_POST['ruta'];
    $database = $_POST['database'];
    $nombre_archivo = $_POST['nombre_archivo'];

    // Asegurarse de que el nombre del archivo termine en .php
    if (!str_ends_with(strtolower($nombre_archivo), '.php')) {
        $nombre_archivo .= '.php';
    }

    // Limpiar y normalizar la ruta
    $ruta = str_replace('\\', DIRECTORY_SEPARATOR, $ruta);
    $ruta = str_replace('/', DIRECTORY_SEPARATOR, $ruta);
    $ruta = rtrim($ruta, DIRECTORY_SEPARATOR);

    // Crear la ruta completa del archivo
    $ruta_completa = $ruta . DIRECTORY_SEPARATOR . $nombre_archivo;

    try {
        // Verificar si la carpeta existe
        if (!is_dir($ruta)) {
            // Intentar crear el directorio con permisos completos
            if (!mkdir($ruta, 0777, true)) {
                throw new Exception("No se pudo crear el directorio: $ruta");
            }
            // Asegurar los permisos después de crear
            chmod($ruta, 0777);
        }

        // Verificar permisos de escritura
        if (!is_writable($ruta)) {
            // Intentar establecer permisos de escritura
            if (!chmod($ruta, 0777)) {
                throw new Exception("No hay permisos de escritura en el directorio: $ruta");
            }
        }

        // Contenido del archivo de conexión
        $contenido = "<?php\n";
        $contenido .= "    \$host = '$host';\n";
        $contenido .= "    \$usuario = '$usuario';\n";
        $contenido .= "    \$password = '$password';\n";
        $contenido .= "    \$database = '$database';\n";
        $contenido .= "    \$conexion = new mysqli(\$host, \$usuario, \$password, \$database);\n\n";
        $contenido .= "    if (\$conexion->connect_error) {\n";
        $contenido .= "        die('Error en la conexión (' . \$conexion->connect_errno . '): ' . \$conexion->connect_error);\n";
        $contenido .= "    }\n\n";
        $contenido .= "    \$conexion->set_charset('utf8');\n";
        $contenido .= "    //Comentar esta linea una ves probada la conexion;\n";
        $contenido .= "     echo 'Conexión exitosa';\n";
        $contenido .= "?>";
  

        // Intentar escribir el archivo
        if (file_put_contents($ruta_completa, $contenido) === false) {
            // Si falla, intentar con fopen
            $fp = fopen($ruta_completa, 'w');
            if ($fp === false) {
                throw new Exception("No se pudo abrir el archivo para escritura: $ruta_completa");
            }
            if (fwrite($fp, $contenido) === false) {
                throw new Exception("No se pudo escribir en el archivo: $ruta_completa");
            }
            fclose($fp);
        }

        // Establecer permisos del archivo
        chmod($ruta_completa, 0666);

        // Respuesta exitosa
        echo json_encode([
            'success' => true,
            'message' => "Archivo de conexion creado exitosamente en: $ruta_completa"
        ]);
    } catch (Exception $e) {
        // Respuesta de error
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método de solicitud no válido'
    ]);
}
?>

