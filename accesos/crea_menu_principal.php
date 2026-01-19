<?php
session_start();
header('Content-Type: application/json');
include "../include/funciones_utilidades.php";



   

    function genera_configuracion($nombreproyecto, $rutaBase, $proyecto) {
        // Verificar si la carpeta 'config' existe, si no, crearla
        $rutaConfig = $rutaBase . '/config';
        if (!file_exists($rutaConfig)) {
            mkdir($rutaConfig, 0777, true);
        }
               // Crea archivo Config del proyecto
               $fecha = date('Y-m-d ');
               $contenido = "<?php\n";
               $contenido .= "/**
        * Archivo de configuración global
        * Contiene constantes y variables de configuración del sistema
        */
       
       require_once __DIR__ . '/../include/version_info.php';
       
       // Información de versión
       define('APP_VERSION', GENERATOR_VERSION);
       define('APP_VERSION_DATE', GENERATOR_VERSION_DATE);
       define('APP_NAME', '".$nombreproyecto."');
       
       // Otras configuraciones globales pueden agregarse aquí
       // define('BASE_URL', 'http://localhost/" . $proyecto . "/');
       // define('DEBUG_MODE', false);
       
       /**
        * Función para obtener información completa de la versión
        * @return string Información formateada de la versión
        */
       function getVersionInfo() {
           return APP_NAME . ' v' . APP_VERSION . ' (' . APP_VERSION_DATE . ')';
       } ";
       
               $rutaConfig = $rutaBase . '/config/config.php';
              // file_put_contents($rutaConfig, $contenido);
                // Crea archivo Config del proyecto
                crearArchivo($rutaConfig, $contenido);
       
    } 

    function generar_headIconos($directorio, $rutaRaiz) {
        $contenido = '<?php
// Detectar ruta base para iconos-web (siempre en la raíz del proyecto)
// Si el archivo actual está en una subcarpeta (vistas, controladores, etc)
$current_path = $_SERVER["PHP_SELF"];
$is_subfolder = (strpos($current_path, "/vistas/") !== false || 
                  strpos($current_path, "/controladores/") !== false || 
                  strpos($current_path, "/modelos/") !== false ||
                  strpos($current_path, "/accesos/") !== false);

// Si estamos dentro de la carpeta "accesos/vistas" o "accesos/controladores", necesitamos subir 2 niveles
$is_deep_subfolder = (strpos($current_path, "/accesos/vistas/") !== false || 
                      strpos($current_path, "/accesos/controladores/") !== false ||
                      strpos($current_path, "/accesos/modelos/") !== false);

if ($is_deep_subfolder) {
    $prefix = "../../";
} elseif ($is_subfolder) {
    $prefix = "../";
} else {
    $prefix = "./";
}
?>
<!-- headIconos.php -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<?php
$favicon = getenv("APP_FAVICON");
if ($favicon) {
    echo "<link rel=\'icon\' href=\'" . $prefix . $favicon . "\' type=\'image/x-icon\'>";
}
?>

<!-- Incluir estilos de iconos Fontello -->
<link href="<?= $prefix ?>iconos-web/css/fontello.css" rel="stylesheet" type="text/css">
<link href="<?= $prefix ?>iconos-web/css/fontello-embedded.css" rel="stylesheet" type="text/css">
<link href="<?= $prefix ?>iconos-web/css/animation.css" rel="stylesheet" type="text/css">
<link href="<?= $prefix ?>iconos-web/css/fontello-codes.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?= $prefix ?>iconos-web/css/estiloIconos.css">
';
	   $parametro = normalizar_ruta($directorio);
		error_log("Directorio: $parametro"); // Imprimir directorio entra parametro
        // Copiar iconos-web a la RAÍZ del proyecto
        $origenIconos = __DIR__ . "/../iconos-web";
        $origenIconos = normalizar_ruta($origenIconos);
        $destinoIconos = $rutaRaiz . "/iconos-web";
        $destinoIconos = normalizar_ruta($destinoIconos);
        
        if (!file_exists($destinoIconos)) {
            copiarCarpeta($origenIconos, $destinoIconos);
        }
        $archivo = "$directorio/headIconos.php";
        // crea el archivo headIconos.php
        crearArchivo($archivo, $contenido);

        // crea el archivo de verificacion de sesion
        $creaverificasesion = "$directorio/verificar_sesion.php";
        $contenido = "<?php
    if (session_status() === PHP_SESSION_NONE) { session_start(); }

    // Determinar la ruta relativa al login de forma dinámica
    \$script_path = str_replace('\\\\', '/', \$_SERVER['SCRIPT_NAME']);
    \$accesos_pos = strpos(\$script_path, '/accesos/');
    
    if (\$accesos_pos !== false) {
        // Si estamos dentro de subcarpetas de accesos
        \$depth = substr_count(substr(\$script_path, \$accesos_pos + 9), '/');
        \$prefix = str_repeat('../', \$depth + 1);
    } else {
        // Raíz o subcarpetas fuera de accesos
        \$prefix = './';
        if (strpos(\$script_path, '/vistas/') !== false) \$prefix = '../';
    }

    \$ruta_login = \$prefix . 'accesos/vistas/vista_login.php';
    \$ruta_cambiar_password = \$prefix . 'accesos/vistas/vista_cambiar_password.php';

    // Verificar si el usuario está autenticado
    if (!isset(\$_SESSION['usuario_id'])) {
        header(\"Location: \$ruta_login\");
        exit;
    }

    // Verificar cambio de clave obligatorio
    if (isset(\$_SESSION['cambio_clave_obligatorio']) && (\$_SESSION['cambio_clave_obligatorio'] == 1 || \$_SESSION['cambio_clave_obligatorio'] === '1')) {
        if (basename(\$_SERVER['PHP_SELF']) !== 'vista_cambiar_password.php') {
            header(\"Location: \$ruta_cambiar_password\");
            exit;
        }
    }

    \$usuario_id = \$_SESSION['usuario_id'] ?? 0;
    \$usuario_nombre = \$_SESSION['usuario_nombre'] ?? 'Invitado';
    \$usuario_perfil = \$_SESSION['usuario_perfil'] ?? '';

    // Cargar variables de entorno (marca, logo, etc)
    if (file_exists(\$prefix . '.env')) {
        \$env = parse_ini_file(\$prefix . '.env');
        if (\$env) {
            foreach (\$env as \$key => \$value) {
                putenv(\"\$key=\$value\");
                \$_ENV[\$key] = \$value;
            }
        }
    }
    ?>";
        crearArchivo($creaverificasesion, $contenido);
    }

    function crearMenuPrincipal($ruta, $conexion) {
        // Usar la ruta proporcionada por el usuario
        $rutaBase = rtrim($ruta, '/') . '/accesos'; // Ruta donde se creará la carpeta 'accesos'
        
        // Crear la carpeta 'accesos' si no existe
        if (!file_exists($rutaBase) && !mkdir($rutaBase, 0777, true)) {
            throw new Exception("No se pudo crear la carpeta: $rutaBase");
        }

        // Crear subcarpetas y copiar archivos
        $carpetas = ['vistas', 'modelos', 'controladores', 'css'];
        foreach ($carpetas as $carpeta) {
            $rutaCarpeta = __DIR__ . "/$carpeta"; // Ruta de la carpeta original
            $rutaDestino = "$rutaBase/$carpeta"; // Ruta de destino
            
            // Agregar depuración para ver las rutas
          //  error_log("Ruta de origen: $rutaCarpeta"); // Imprimir ruta de origen
          //  error_log("Ruta de destino: $rutaDestino"); // Imprimir ruta de destino

            // Copiar la carpeta y su contenido
            if (is_dir($rutaCarpeta)) {
                copiarCarpeta($rutaCarpeta, $rutaDestino);
            }
            // copia archivo de headicon para direccionar los iconos del menu accesos
            //$rutaCarpeta = __DIR__ ."/headIconos.php";
            //copiarArchivo($origen, $rutaDestino);
            
        }

        // crea archivo de headIcon.php para el modulo de accesos
        // Crear headIconos.php pasando la ruta raíz para la copia de iconos
        generar_headIconos($rutaBase, $ruta);
 
        // Crear el archivo index.php raíz con validación de sesión
        $rutaPrincipalProyecto = rtrim($ruta, '/') . '/index.php';
        $contenido = "<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Verificar si el usuario está autenticado
if (!isset(\$_SESSION['usuario_id'])) {
    header('Location: accesos/vistas/vista_login.php');
    exit();
}

// Redirigir a la vista del menú dinámico si está autenticado
header('Location: accesos/vistas/vista_menu_principal.php');
exit();";
        file_put_contents($rutaPrincipalProyecto, $contenido);
        // Obtener el nombre de la carpeta del proyecto (último segmento de la ruta)
        $ruta_norm = str_replace('\\', '/', $ruta);
        $rutaArray = explode('/', rtrim($ruta_norm, '/'));
        $proyecto = end($rutaArray);
        // Actualizar rutas de forma inteligente (Administración vs CRUDs)
        actualizaRutaProgramas($proyecto, $conexion);


        $directorio = $rutaBase . '/modelos'; // Directorio a escanear
        $patron = '*.php'; // Patrón de archivos a buscar
        $cadena_a_busca = '<conexion.php>'; // Cadena a buscar en los archivos
        $palabra_reemplazo = $_SESSION['nombre_archivo']; // Palabra a reemplazar
      
        $archivos = listarArchivos($directorio, $patron);
        foreach ($archivos as $archivo) {
            reemplazarEnArchivo($archivo, $cadena_a_busca, $palabra_reemplazo);
        }
        
        // remplaza en vista_menu_principal el titulo del menu principal por el nombre del proyecto
        $nombreproyecto = $_SESSION['nombre_proyecto'];
        // si nombreproyecto no existe se asigna el $proyecto como nombreproyecto
        if (empty($nombreproyecto)) {
            $nombreproyecto = $proyecto;
        }
        $nombremenubuscar = '**Menu Principal**';
        $nombremenureemplazo = $nombreproyecto;
        $path_vista_menu_principal = $rutaBase . '/vistas';
        $vista_menu_principal = 'vista_menu_principal.php';
        $archivo = listarArchivos($path_vista_menu_principal, $vista_menu_principal);

        error_log("archivo Menu Principal: $archivo[0]"); 
        reemplazarEnArchivo($archivo[0], $nombremenubuscar, $nombremenureemplazo);

        // remplaza en vista_roles_programas
        $path_vista_roles_programas = $rutaBase . '/vistas';
        $vista_roles_programas = 'vista_roles_programas.php';
        $archivo = listarArchivos($path_vista_roles_programas, $vista_roles_programas);

        reemplazarEnArchivo($archivo[0], $cadena_a_busca, $palabra_reemplazo);
        
    }


try {
    // Incluir el archivo de conexión
    if (!isset($_SESSION['ruta']) || !isset($_SESSION['nombre_archivo'])) {
        throw new Exception("Configuración de conexión no encontrada");
    }
    $ruta = $_SESSION['ruta'];
    $archivo_conexion = $ruta . '/' . $_SESSION['nombre_archivo'];
    
    
    if (!file_exists($archivo_conexion)) {
        throw new Exception("Archivo de conexión no encontrado");
    }

    require_once($archivo_conexion);
    
    // Verificar la conexión
    if (!isset($conexion)) {
        throw new Exception("Error en la conexión a la base de datos");
    }

    // Actualizar la ruta de programas y obtener la respuesta
    // Obtener el nombre de la carpeta del proyecto de forma robusta
    $ruta_norm = str_replace('\\', '/', $ruta);
    $rutaArray = explode('/', rtrim($ruta_norm, '/'));
    $proyecto = end($rutaArray);
    $rutaProyecto = $proyecto . '/accesos/vistas';

    $nombreproyecto = $_SESSION['nombre_proyecto'];
    // si nombreproyecto no existe se asigna el $proyecto como nombreproyecto
    if (empty($nombreproyecto)) {
        $nombreproyecto = $proyecto;
    }

    // Se crea las carpetas del menú principal
    crearMenuPrincipal($ruta, $conexion);

    // Sincronizar automáticamente los programas (CRUDs) generados
    $rutaVistasSinc = $ruta . '/vistas';
    $rutaRelativaVistas = '/' . $proyecto . '/vistas';
    sincronizar_programas_vistas($conexion, $rutaVistasSinc, $rutaRelativaVistas);

    $actualizaRutaResponse = actualizaRutaProgramas($proyecto, $conexion);
    // crea archivo de configuracion
    genera_configuracion($nombreproyecto, $ruta, $proyecto);

    error_log("rutaProyecto: $rutaProyecto"); // Imprimir rutaProyecto entra parametro

    // Agregar el mensaje de éxito
    $response = [
        'success' => true,
        'message' => 'El menú principal se creó exitosamente.',
        'actualizaRutaResponse' => $actualizaRutaResponse
    ];
    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
