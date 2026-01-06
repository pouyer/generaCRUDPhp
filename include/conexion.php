<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

    // Verificar si se seleccionó una base de datos
    // Configuración de la conexión
    
    // Cargar variables de entorno si la función existe (funciones_utilidades.php debería estar incluido)
    if (function_exists('cargar_env')) {
        $envLoaded = cargar_env(__DIR__ . '/../.env');
        if (!$envLoaded) {
            error_log("Advertencia: No se pudo cargar el archivo .env");
        }
    }

    // Prioridad: POST > SESSION > ENV > Default (Usando ?? para evitar Undefined Key y ?: para ignorar vacíos)
    $servidor = (($_POST['host'] ?? '') ?: ($_SESSION['db_host'] ?? '') ?: getenv('DB_HOST')) ?: 'localhost';
    $usuario  = (($_POST['usuario'] ?? '') ?: ($_SESSION['db_user'] ?? '') ?: getenv('DB_USER')) ?: 'root';
    $password = $_POST['password'] ?? $_SESSION['db_pass'] ?? getenv('DB_PASS') ?? '';
    $puerto   = (($_POST['puerto'] ?? '') ?: ($_SESSION['db_port'] ?? '') ?: getenv('DB_PORT')) ?: 3306;

    // Fix: En Windows, 'localhost' usa pipes y ignora el puerto. Si se usa un puerto no estándar (ej: Docker 3308),
    // se debe forzar la IP 127.0.0.1 para usar TCP/IP.
    if ($servidor === 'localhost' && $puerto != 3306) {
        $servidor = '127.0.0.1';
    }

    try {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['base_datos']) && !empty($_POST['base_datos'])) {
            $dbname = $_POST['base_datos'];
            $conexion = new mysqli($servidor, $usuario, $password, $dbname, $puerto);
        } else {
            // Manejar el caso cuando no se ha enviado el formulario
            $conexion = new mysqli($servidor, $usuario, $password, null, $puerto);
        }
    } catch (Throwable $e) {
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
        
        // Fallback: Intentar conectar sin seleccionar base de datos para permitir que cargue la lista
        try {
            $conexion = new mysqli($servidor, $usuario, $password, null, $puerto);
            
            // Si funciona el fallback, agregamos un mensaje visual pero NO matamos el script
            // CRITICAL: Solo mostrar alerta si NO es una petición AJAX para no romper el JSON
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
            if (!$isAjax && isset($dbname)) {
                echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                        <strong>Advertencia:</strong> No se pudo conectar a la base de datos '<strong>" . htmlspecialchars($dbname) . "</strong>'. 
                        <br>Se ha establecido una conexión general al servidor. Por favor, selecciona la base de datos correcta de la lista.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
            }
                  
        } catch (Throwable $e2) {
             // Si falla también la conexión básica, entonces sí es fatal
            $detalle = htmlspecialchars($e2->getMessage());
            die("<div class='alert alert-danger'>
                <h4>Error de Conexión Fatal</h4>
                <p>No se pudo conectar al servidor de base de datos.</p>
                <p><strong>Detalle:</strong> $detalle</p>
                <p><em>Sugerencia: Verifica Host, Usuario, Contraseña y Puerto. Recuerda usar '127.0.0.1' para puertos personalizados.</em></p>
            </div>");
        }
    }
    
    // Verificar si la conexión fue exitosa
    if ($conexion->connect_errno) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    
    // Establecer charset a utf8mb4 para manejar caracteres especiales
    $conexion->set_charset("utf8mb4");
?>