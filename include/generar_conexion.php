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
    //$ruta .= '/configuracion' ;
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
        // Generar archivo .env
        $contenidoEnv = "DB_HOST=$host\n";
        $contenidoEnv .= "DB_USER=$usuario\n";
        $contenidoEnv .= "DB_PASS=$password\n";
        $contenidoEnv .= "DB_NAME=$database\n";
        $contenidoEnv .= "DB_PORT=" . ($_POST['puerto'] ?? '3306') . "\n";
        $contenidoEnv .= "SMTP_HOST=" . trim($_POST['smtp_host'] ?? '') . "\n";
        $contenidoEnv .= "SMTP_USER=" . trim($_POST['smtp_user'] ?? '') . "\n";
        $contenidoEnv .= "SMTP_PASS=" . trim($_POST['smtp_pass'] ?? '') . "\n";
        $contenidoEnv .= "SMTP_PORT=" . trim($_POST['smtp_port'] ?? '587') . "\n";
        $contenidoEnv .= "SMTP_FROM=" . trim($_POST['smtp_from'] ?? '') . "\n";
        $contenidoEnv .= "APP_TIMEZONE=" . trim($_POST['timezone'] ?? 'America/Bogota') . "\n";
        
        // --- Procesamiento de Imágenes ---
        $imgDir = $ruta . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img';
        if (!is_dir($imgDir)) {
            mkdir($imgDir, 0777, true);
        }

        function procesarImagen($fileKey, $envKey, $imgDir, $filenameInput) {
            $envLine = "";
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES[$fileKey]['tmp_name'];
                $originalName = basename($_FILES[$fileKey]['name']);
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'ico'];
                
                if (in_array($ext, $allowed)) {
                    // Usar nombre fijo si se prefiere, o original
                    $finalName = $filenameInput . '.' . $ext;
                    $destPath = $imgDir . DIRECTORY_SEPARATOR . $finalName;
                    
                    if (move_uploaded_file($tmpName, $destPath)) {
                        chmod($destPath, 0666);
                        // Ruta relativa para la web (assets/img/...)
                        $webPath = 'assets/img/' . $finalName; 
                        $envLine = "$envKey=$webPath\n";
                    }
                }
            }
            return $envLine;
        }

        $contenidoEnv .= procesarImagen('logo_app', 'APP_LOGO', $imgDir, 'logo');
        $contenidoEnv .= procesarImagen('bg_login', 'LOGIN_BG', $imgDir, 'login_bg');
        $contenidoEnv .= procesarImagen('favicon', 'APP_FAVICON', $imgDir, 'favicon');
        // --- Fin Procesamiento de Imágenes ---
        
        $rutaEnv = $ruta . DIRECTORY_SEPARATOR . '.env';
        
        if (file_put_contents($rutaEnv, $contenidoEnv) === false) {
             throw new Exception("No se pudo crear el archivo .env en: $rutaEnv");
        }
        chmod($rutaEnv, 0666);

        // Contenido del archivo de conexión (leeyendo .env)
        $contenido = "<?php\n";
        $contenido .= "    /**\n";
        $contenido .= "     * Carga variables de entorno desde un archivo .env\n";
        $contenido .= "     */\n";
        $contenido .= "    if (!function_exists('cargar_env')) {\n";
        $contenido .= "        function cargar_env(\$ruta) {\n";
        $contenido .= "            if (!file_exists(\$ruta)) {\n";
        $contenido .= "                return false;\n";
        $contenido .= "            }\n";
        $contenido .= "            \$lineas = file(\$ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);\n";
        $contenido .= "            foreach (\$lineas as \$linea) {\n";
        $contenido .= "                if (strpos(trim(\$linea), '#') === 0) continue;\n";
        $contenido .= "                list(\$nombre, \$valor) = explode('=', \$linea, 2);\n";
        $contenido .= "                \$nombre = trim(\$nombre);\n";
        $contenido .= "                \$valor = trim(\$valor);\n";
        $contenido .= "                putenv(sprintf('%s=%s', \$nombre, \$valor));\n";
        $contenido .= "                \$_ENV[\$nombre] = \$valor;\n";
        $contenido .= "                \$_SERVER[\$nombre] = \$valor;\n";
        $contenido .= "            }\n";
        $contenido .= "            return true;\n";
        $contenido .= "        }\n";
        $contenido .= "    }\n\n";
        $contenido .= "    // Cargar variables del .env\n";
        $contenido .= "    cargar_env(__DIR__ . '/.env');\n\n";
        $contenido .= "    // Configurar Zona Horaria\n";
        $contenido .= "    date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'UTC');\n\n";
        $contenido .= "    \$host = getenv('DB_HOST') ?: 'localhost';\n";
        $contenido .= "    \$usuario = getenv('DB_USER');\n";
        $contenido .= "    \$password = getenv('DB_PASS');\n";
        $contenido .= "    \$database = getenv('DB_NAME');\n";
        $contenido .= "    \$puerto = getenv('DB_PORT') ?: 3306;\n\n";
        
        $contenido .= "    \$conexion = new mysqli(\$host, \$usuario, \$password, \$database, \$puerto);\n\n";
        $contenido .= "    if (\$conexion->connect_error) {\n";
        $contenido .= "        die('Error en la conexión (' . \$conexion->connect_errno . '): ' . \$conexion->connect_error);\n";
        $contenido .= "    }\n\n";
        $contenido .= "    \$conexion->set_charset('utf8mb4');\n\n";
        $contenido .= "    // Sincronizar zona horaria con la base de datos\n";
        $contenido .= "    \$ahora = new DateTime();\n";
        $contenido .= "    \$offset = \$ahora->format('P');\n";
        $contenido .= "    \$conexion->query(\"SET time_zone='\$offset'\");\n";
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

