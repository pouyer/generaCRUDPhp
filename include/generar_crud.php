<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
function generar_crud_completo($tabla, $baseDatos, $ruta, $nombre_archivo, $tipo_tabla) {
    try {
        // Validar parámetros
        if (empty($tabla) || empty($baseDatos) || empty($ruta) || empty($nombre_archivo) || empty($tipo_tabla)) {
            throw new Exception("Todos los parámetros son requeridos");
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

        // Generamos el modelo pasando el parámetro es_vista
        $resultadoModelo = generar_modelo($tabla, $campos, $dirModelos, $nombre_archivo, $es_vista);
        if ($resultadoModelo !== true) {
            throw new Exception("Error al generar el modelo");
        }

        // Generamos la vista pasando el parámetro es_vista
        $resultadoVista = generar_vista($tabla, $campos, $dirVistas, $es_vista);
        if ($resultadoVista !== true) {
            throw new Exception("Error al generar la vista");
        }

        // Generamos el css
        $resultadoCss = generar_vista_css($dirCss);
        if ($resultadoCss !== true) {
            throw new Exception("Error al generar el css");
        }

        // Generamos el controlador pasando el parámetro es_vista
        $resultadoControlador = generar_controlador($tabla, $campos, $dirControladores, $nombre_archivo, $es_vista);
        if ($resultadoControlador !== true) {
            throw new Exception("Error al generar el controlador");
        }
        
        return true;

    } catch (Exception $e) {
        return "Error en " . ($es_vista ? "vista" : "tabla") . " $tabla: " . $e->getMessage();
    }
}

try {
    $resultados = [];
    foreach ($_POST['tabla'] as $key => $tabla) {
        $tipo_tabla = $_POST['tipo_tabla'][$key];
        $resultado = generar_crud_completo(
            $tabla,
            $_POST['base_datos'],
            $_POST['ruta'],
            $_POST['nombre_archivo'],
            $tipo_tabla
        );
        
        if ($resultado === true) {
            $resultados[] = "CRUD generado exitosamente para la " . ($tipo_tabla === 'VIEW' ? 'vista' : 'tabla') . ": $tabla";
        } else {
            $resultados[] = $resultado;
        }
    }

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