<?php
session_start();
header('Content-Type: application/json');
include "../include/funciones_utilidades.php";


    function actualizaRutaProgramas($ruta, $conexion) {
        $response = []; // Inicializar la respuesta

        $response['ruta_ingresa'] = $ruta; // Imprimir ruta de origen

        // Preparar la consulta SQL
        $query = "UPDATE `acc_programa` SET `ruta` = ? WHERE (`id_programas` in (1,2,3,4,5))";
        $stmt = $conexion->prepare($query);
        
        // Verificar si la preparación fue exitosa
        if (!$stmt) {
            $response['error'] = "Error en la preparación de la consulta: " . $conexion->error;
            echo json_encode($response);
            return false;
        }
        $ruta = '/' . $ruta;
        // Vincular el parámetro y ejecutar la consulta
        $stmt->bind_param('s', $ruta);
        if ($stmt->execute()) {
            $response['success'] = "Ruta actualizada exitosamente.";
            echo json_encode($response);
            return true;
        } else {
            $response['error'] = "Error al ejecutar la consulta: " . $stmt->error;
            echo json_encode($response);
            return false;
        }
    }
   
    function generar_headIconos($directorio) {
        $contenido = '    <!-- headIconos.php -->
    <!-- Incluir estilos de bootstrap -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <!-- Incluir estilos de iconos -->
        <link href="../../iconos-web/css/iconos_web_fontello.css" rel="stylesheet" type="text/css">
        <link href="../../iconos-web/css/iconos_web_fontello-embedded.css" rel="stylesheet" type="text/css">
        <link href="../../iconos-web/css/animation.css" rel="stylesheet" type="text/css">
        <link href="../../iconos-web/css/iconos_web_fontello-codes.css" rel="stylesheet" type="text/css">
    
        <link rel="stylesheet" href="../../iconos-web/css/estiloIconos.css">
    
    <!-- Otros estilos o scripts que necesites -->';
        if (!is_dir("../../iconos-web")) {
               // genera ruta de iconos que puede usar la aplicacion
                $origenIconos = __DIR__ . "/../../iconos-web"; // Cambiar la ruta para subir un nivel
                $origenIconos = normalizar_ruta($origenIconos);
                //error_log("Ruta de origen: $origenIconos"); // Imprimir ruta de origen
                $ruta = $_POST['ruta'];
                $destinoIconos =  $ruta . "/iconos-web";
                $destinoIconos = normalizar_ruta($destinoIconos);
                //error_log("Ruta de destino: $destinoIconos"); // Imprimir ruta de destino
                copiarCarpeta($origenIconos, $destinoIconos);
        }
        $archivo = "$directorio/headIconos.php";
        return file_put_contents($archivo, $contenido) !== false;
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
    // Se crea las carpetas del menu principal
    crearMenuPrincipal($ruta, $conexion);
   // Agregar el mensaje de éxito
    echo json_encode([
        'success' => true,
        'message' => 'El menú principal se creó exitosamente.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
