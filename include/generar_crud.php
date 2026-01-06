<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Verificar que tenemos todos los datos necesarios
$missingFields = [];

if (!isset($_POST['tabla'])) {
    $missingFields[] = 'tabla';
}
if (!isset($_POST['tipo_tabla'])) {
    $missingFields[] = 'tipo_tabla';
}
if (!isset($_POST['base_datos'])) {
    $missingFields[] = 'base_datos';
}
if (!isset($_POST['ruta'])) {
    $missingFields[] = 'ruta';
}
if (!isset($_POST['nombre_archivo'])) {
    $missingFields[] = 'nombre_archivo';
}

if (!empty($missingFields)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => "Error: Faltan los siguientes campos necesarios para generar el CRUD: " . implode(', ', $missingFields)
    ]);
    exit;
}

// Incluir todos los archivos necesarios
require_once('funciones_utilidades.php');
require_once('conexion.php');
require_once('generar_modelo.php');
require_once('generar_vista.php');
require_once('generar_controlador.php');

// Función principal para generar el CRUD completo
function generar_crud_completo($tabla, $baseDatos, $ruta, $nombre_archivo, $tipo_tabla, $config_tabla = []) {
    $es_vista = false;
    try {
        // Validar parámetros
        if (empty($tabla) || empty($baseDatos) || empty($ruta) || empty($nombre_archivo) || empty($tipo_tabla)) {
             $faltantes = [];
             if(empty($tabla)) $faltantes[] = "tabla";
             if(empty($baseDatos)) $faltantes[] = "baseDatos";
             if(empty($ruta)) $faltantes[] = "ruta";
             if(empty($nombre_archivo)) $faltantes[] = "nombre_archivo";
             if(empty($tipo_tabla)) $faltantes[] = "tipo_tabla"; // Puede fallar si tipo_tabla es 0 o string vacio
             
            throw new Exception("Todos los parámetros son requeridos. Faltan: " . implode(', ', $faltantes));
        }

        // Convertir tipo_tabla a booleano para mejor manejo
        $es_vista = ($tipo_tabla === 'VIEW');

        // Obtener la estructura de la tabla
        global $conexion;
        $conexion->select_db($baseDatos);
        
        $query = "DESCRIBE $tabla";
        $resultado = $conexion->query($query);
        
        if (!$resultado) {
            throw new Exception("Error al obtener la estructura de la tabla: " . $conexion->error);
        }

        $campos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $campos[] = $fila;
        }

        if (empty($campos)) {
            throw new Exception("No se encontraron campos en la tabla");
        }

        // Crear directorios si no existen
        $dirModelos = $ruta . "/modelos";
        $dirVistas = $ruta . "/vistas";
        $dirControladores = $ruta . "/controladores";
        $dirCss = $ruta . "/css";
        

        foreach ([$dirModelos, $dirVistas, $dirControladores, $dirCss] as $dir) {
            if (!file_exists($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    throw new Exception("Error al crear el directorio: $dir");
                }
            }
        }

        // Generamos el modelo pasando el parámetro es_vista y la configuración de la tabla
        $resultadoModelo = generar_modelo($tabla, $campos, $dirModelos, $nombre_archivo, $es_vista, $config_tabla);
        if ($resultadoModelo !== true) {
            throw new Exception("Error al generar el modelo");
        }

        // Generamos la vista pasando el parámetro es_vista y la configuración de la tabla
        $resultadoVista = generar_vista($tabla, $campos, $dirVistas, $es_vista, $config_tabla);
        if ($resultadoVista !== true) {
            throw new Exception("Error al generar la vista");
        }

        // Generamos el css
        $resultadoCss = generar_vista_css($dirCss);
        if ($resultadoCss !== true) {
            throw new Exception("Error al generar el css");
        }

        // Generamos el controlador pasando el parámetro es_vista y la configuración de la tabla
        $resultadoControlador = generar_controlador($tabla, $campos, $dirControladores, $nombre_archivo, $es_vista, $config_tabla);
        if ($resultadoControlador !== true) {
            throw new Exception("Error al generar el controlador");
        }
        
        return true;

    } catch (Exception $e) {
        return "Error en " . ($es_vista ? "vista" : "tabla") . " $tabla: " . $e->getMessage();
    }
}

function generar_incluye_iconos($directorio) {
    $contenido = '    <!-- headIconos.php -->
    <!-- Incluir estilos de bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Incluir estilos de iconos -->
    <link href="../iconos-web/css/fontello.css" rel="stylesheet" type="text/css">
    <link href="../iconos-web/css/fontello-embedded.css" rel="stylesheet" type="text/css">
    <link href="../iconos-web/css/animation.css" rel="stylesheet" type="text/css">
    <link href="../iconos-web/css/fontello-codes.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../iconos-web/css/estiloIconos.css">
    <!-- Otros estilos o scripts que necesites -->';
    $archivo = "$directorio/headIconos.php";
    return file_put_contents($archivo, $contenido) !== false;
}

try {
    $resultados = [];
    $config_tablas = isset($_POST['config_tablas']) ? json_decode($_POST['config_tablas'], true) : [];
    // Configuraciones globales de apariencia
    $config_tema = $_POST['config_tema'] ?? 'azul';
    $config_color = $_POST['config_color'] ?? '#1e3c72';
    $config_icono = $_POST['config_icono'] ?? 'icon-table';
    
    foreach ($_POST['tabla'] as $key => $tabla) {
        $tipo_tabla = $_POST['tipo_tabla'][$key];
        $config_tabla = isset($config_tablas[$tabla]) ? $config_tablas[$tabla] : [];
        // Añadir configuraciones al config de tabla (priorizando la específica de la tabla)
        $config_tabla['tema'] = $config_tabla['tema'] ?? $config_tema;
        $config_tabla['color'] = $config_tabla['color'] ?? $config_color;
        $config_tabla['icono'] = $config_tabla['icono'] ?? $config_icono;

        $resultado = generar_crud_completo(
            $tabla,
            $_POST['base_datos'],
            $_POST['ruta'],
            $_POST['nombre_archivo'],
            $tipo_tabla,
            $config_tabla
        );
        
        if ($resultado === true) {
            $resultados[] = "CRUD generado exitosamente para la " . ($tipo_tabla === 'VIEW' ? 'vista' : 'tabla') . ": $tabla";
        } else {
            $resultados[] = $resultado;
        }
    }

   // genera ruta de iconos que puede usar la aplicacion
    $origenIconos = __DIR__ . "/../iconos-web"; // Cambiar la ruta para subir un nivel
    $origenIconos = normalizar_ruta($origenIconos);
    //error_log("Ruta de origen: $origenIconos"); // Imprimir ruta de origen
    $ruta = $_POST['ruta'];
    $destinoIconos =  $ruta . "/iconos-web";
    $destinoIconos = normalizar_ruta($destinoIconos);
    //error_log("Ruta de destino: $destinoIconos"); // Imprimir ruta de destino
    copiarCarpeta($origenIconos, $destinoIconos);
    generar_incluye_iconos($ruta);



    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'messages' => $resultados
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>