<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
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

    // Prioridad para el funcionamiento interno del GENERADOR: ENV > POST > SESSION > Default 
    // Esto garantiza que el generador siempre use su propio .env si está presente.
    $servidor = (getenv('DB_HOST') ?: ($_POST['host'] ?? '') ?: ($_SESSION['db_host'] ?? '')) ?: 'localhost';
    $usuario  = (getenv('DB_USER') ?: ($_POST['usuario'] ?? '') ?: ($_SESSION['db_user'] ?? '')) ?: 'root';
    $password = (getenv('DB_PASS') !== false) ? getenv('DB_PASS') : ((isset($_POST['password']) && $_POST['password'] !== '') ? $_POST['password'] : ($_SESSION['db_pass'] ?? ''));
    $puerto   = (getenv('DB_PORT') ?: ($_POST['puerto'] ?? '') ?: ($_SESSION['db_port'] ?? '')) ?: 3306;

    // Solo forzar 127.0.0.1 si estamos en Windows y el puerto no es el estándar
    // o si el usuario específicamente tiene problemas con 'localhost' y sockets.
    // En Linux, 'localhost' y '127.0.0.1' pueden tener permisos distintos en MySQL.
    if ($servidor === 'localhost' && $puerto != 3306 && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $servidor = '127.0.0.1';
    }

    // Depuración (solo para logs del servidor, enmascarando password)
    $pass_status = empty($password) ? 'vacia' : 'presente (len:' . strlen($password) . ')';
    // error_log("Conectando a $servidor:$puerto con usuario $usuario y pass $pass_status");

    try {
        $dbname = ($_POST['base_datos'] ?? '') ?: ($_SESSION['base_datos'] ?? '');
        
        if (!empty($dbname)) {
            $conexion = new mysqli($servidor, $usuario, $password, $dbname, $puerto);
        } else {
            // Conectar sin base de datos si no hay ninguna especificada
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
            $detalle = $e2->getMessage();
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
            
            if ($isAjax) {
                header('Content-Type: application/json');
                die(json_encode(['success' => false, 'error' => "Error de Conexión Fatal: $detalle"]));
            }

            $detalle_html = htmlspecialchars($detalle);
            die("<div class='alert alert-danger'>
                <h4>Error de Conexión Fatal</h4>
                <p>No se pudo conectar al servidor de base de datos.</p>
                <p><strong>Detalle:</strong> $detalle_html</p>
                <p><em>Sugerencia: Verifica Host, Usuario, Contraseña y Puerto. Recuerda usar '127.0.0.1' para puertos personalizados.</em></p>
            </div>");
        }
    }
    
    // Verificar si la conexión fue exitosa
    if ($conexion->connect_errno) {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        if ($isAjax) {
            header('Content-Type: application/json');
            die(json_encode(['success' => false, 'error' => "Error de conexión: " . $conexion->connect_error]));
        }
        die("Error de conexión: " . $conexion->connect_error);
    }
    
    // Establecer charset a utf8mb4 para manejar caracteres especiales
    $conexion->set_charset("utf8mb4");
?>