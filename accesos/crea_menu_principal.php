<?php
session_start();
header('Content-Type: application/json');
include "../include/funciones_utilidades.php";


    function actualizaRutaProgramas($ruta, $conexion) {
        $response = []; // Inicializar la respuesta

        $response['ruta_ingresa'] = $ruta; // Imprimir ruta de origen

        // Preparar la consulta SQL
        $query = "UPDATE `acc_programa` SET `ruta` = ? WHERE (`id_programas` in (1,2,3,4,5,6))";
        $stmt = $conexion->prepare($query);
        
        // Verificar si la preparación fue exitosa
        if (!$stmt) {
            $response['error'] = "Error en la preparación de la consulta: " . $conexion->error;
            return $response; // Devolver el error
        }
        $ruta = '/' . $ruta;
        // Vincular el parámetro y ejecutar la consulta
        $stmt->bind_param('s', $ruta);
        if ($stmt->execute()) {
            $response['success'] = "Ruta actualizada exitosamente.";
        } else {
            $response['error'] = "Error al ejecutar la consulta: " . $stmt->error;
        }
        return $response; // Devolver la respuesta
    }
   
    function crearArchivo($archivo, $contenido) {
        $fp = fopen($archivo, 'w');
        fwrite($fp, $contenido);
        fclose($fp);
    }

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
       
       // Información de versión
       define('APP_VERSION', '1.0.0');
       define('APP_VERSION_DATE', '".$fecha."');
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

    function generar_headIconos($directorio) {
        $contenido = '    <!-- headIconos.php -->
    <!-- Incluir estilos de bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> 
    <!-- Incluir estilos de iconos -->
		<link href="../iconos-web/css/iconos_web_fontello.css" rel="stylesheet" type="text/css">
		<link href="../iconos-web/css/iconos_web_fontello-embedded.css" rel="stylesheet" type="text/css">
		<link href="../iconos-web/css/animation.css" rel="stylesheet" type="text/css">
		<link href="../iconos-web/css/iconos_web_fontello-codes.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="../iconos-web/css/estiloIconos.css">
    
    <!-- Otros estilos o scripts que necesites -->';
	   $parametro = normalizar_ruta($directorio);
		error_log("Directorio: $parametro"); // Imprimir directorio entra parametro
        if (!is_dir("../../iconos-web")) {
               // genera ruta de iconos que puede usar la aplicacion
                $origenIconos = __DIR__ . "/../iconos-web"; // Cambiar la ruta para subir un nivel
                $origenIconos = normalizar_ruta($origenIconos);
                error_log("Ruta de origen: $origenIconos"); // Imprimir ruta de origen
                $ruta = $parametro;
                $destinoIconos =  $ruta . "/iconos-web";
                $destinoIconos = normalizar_ruta($destinoIconos);
                error_log("Ruta de destino: $destinoIconos"); // Imprimir ruta de destino
                copiarCarpeta($origenIconos, $destinoIconos);
        }
        $archivo = "$directorio/headIconos.php";
        // crea el archivo headIconos.php
        crearArchivo($archivo, $contenido);

        // crea el archivo de verificacion de sesion
        $creaverificasesion = "$directorio/verificar_sesion.php";
        $contenido = "<?php
session_start();
// Verificar si la sesión está activa
// Obtener información del usuario para uso en las páginas
// Usando operador de fusión de null (??) o verificando con isset para evitar avisos
\$usuario_id = \$_SESSION['usuario_id'] ?? 0; // Asignar 0 si no está definido
\$usuario_nombre = \$_SESSION['usuario_nombre'] ?? 'sin login';
\$usuario_perfil = \$_SESSION['usuario_perfil'] ?? '';

// Otras variables de sesión según sea necesario
// ";
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
        generar_headIconos($rutaBase);
 
        // Crear el archivo index.php
        $contenido = "<?php\n// Redirigir a la vista del menú dinámico\nheader('Location: accesos/vistas/vista_menu_principal.php');\nexit();";
        $rutaPrincipalProyecto = rtrim($ruta, '/') . '/index.php';
        file_put_contents($rutaPrincipalProyecto, $contenido);

        $rutaArray = explode('\\', $ruta); // Separar por '\'
        $proyecto = end($rutaArray);
        $rutaProyecto = $proyecto.'/accesos/vistas';
        actualizaRutaProgramas($rutaProyecto,$conexion);


        $directorio = $rutaBase . '/modelos'; // Directorio a escanear
        $patron = '*.php'; // Patrón de archivos a buscar
        $cadena_a_busca = '<conexion.php>'; // Cadena a buscar en los archivos
        $palabra_reemplazo = $_SESSION['nombre_archivo']; // Palabra a reemplazar
      
        $archivos = listarArchivos($directorio, $patron);
        foreach ($archivos as $archivo) {
            reemplazarEnArchivo($archivo, $cadena_a_busca, $palabra_reemplazo);
        }
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

    // Se crea las carpetas del menú principal
    crearMenuPrincipal($ruta, $conexion);

    // Actualizar la ruta de programas y obtener la respuesta
    $rutaArray = explode('\\', $ruta); // Separar por '\'



    $proyecto = end($rutaArray);
    $rutaProyecto = $proyecto . '/accesos/vistas';

    $nombreproyecto = $_SESSION['nombre_proyecto'];
    // si nombreproyecto no existe se asigna el $proyecto como nombreproyecto
    if (empty($nombreproyecto)) {
        $nombreproyecto = $proyecto;
    }

    $actualizaRutaResponse = actualizaRutaProgramas($rutaProyecto, $conexion);
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
