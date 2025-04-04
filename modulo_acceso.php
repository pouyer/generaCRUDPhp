<?php
session_start();
require_once('include/funciones_utilidades.php');
//require_once('./accesos/controladores/controlador_menu_principal.php');

// Verificar si existe la ruta en la sesión
$ruta = isset($_SESSION['ruta']) ? $_SESSION['ruta'] : '';
$baseDatos = isset($_SESSION['base_datos']) ? $_SESSION['base_datos'] : '';
$nombrearchivo = isset($_SESSION['nombre_archivo']) ? $_SESSION['nombre_archivo'] : '';
$nombreproyecto = isset($_SESSION['nombre_proyecto']) ? $_SESSION['nombre_proyecto'] : ''; // Recuperar nombre_proyecto

$resultadoActualizacion = ''; // Variable para almacenar el resultado de la actualización

// Mostrar la alerta si falta información
if (empty($ruta) || empty($nombrearchivo) || empty($baseDatos)) { // No validar nombre_proyecto como obligatorio
    echo "<script>alert('Faltan datos: Asegúrese de que la ruta del proyecto, el archivo de conexión y la base de datos estén configurados.');</script>";
}

// Llamada a la función para crear el menú principal
if (isset($_POST['crear_menu'])) {
    $controlador = new ControladorMenuPrincipal();
    $resultadoActualizacion = $controlador->crearMenuPrincipal($ruta);
    // Decodificar el resultado JSON
    $resultadoJson = json_decode($resultadoActualizacion, true);
    if (isset($resultadoJson['success'])) {
        $resultadoActualizacion = $resultadoJson['success'];
    } elseif (isset($resultadoJson['error'])) {
        $resultadoActualizacion = $resultadoJson['error'];
    }
}

/*
// Función para ejecutar el script SQL
function ejecutar_script_sql($conexion, $archivo_sql) {
    try {
        $sql = file_get_contents($archivo_sql);
        $sentencias = explode(';', $sql);
        
        foreach ($sentencias as $sentencia) {
            $sentencia = trim($sentencia);
            if (!empty($sentencia)) {
                if (!$conexion->query($sentencia)) {
                    throw new Exception("Error ejecutando sentencia: " . $conexion->error);
                }
            }
        }
        return ["success" => true, "message" => "Tablas de acceso creadas exitosamente"];
    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}
*/
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Acceso</title>

    <?php include('headIconos.php'); // Incluir los elementos del encabezado iconos?>
</head>
<body>
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h1 class="text-center">Módulo de Acceso</h1>
                <a href="index.php" class="btn btn-secondary mb-4">← Volver</a>
                <?php if (!empty($ruta) && !empty($nombrearchivo) && !empty($baseDatos)): ?>
                    <div class="alert alert-info">
                        Ruta del proyecto: <?php echo htmlspecialchars($ruta); ?><br>
                        Archivo de conexión: <?php echo htmlspecialchars($nombrearchivo); ?><br>
                        Base de datos seleccionada: <?php echo htmlspecialchars($baseDatos); ?><br>
                        Nombre del proyecto: <?php echo htmlspecialchars($nombreproyecto); ?> <!-- Mostrar nombre_proyecto -->
                    </div>
                <?php endif; ?>
                <?php if ($resultadoActualizacion): ?>
                    <div class="alert alert-info">
                        <?php echo htmlspecialchars($resultadoActualizacion); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="list-group">
                    <a href="#" onclick="crearBaseDatos()" class="list-group-item list-group-item-action">
                        <h5 class="mb-1 icon-database-3">Crear Base de Datos de Accesos</h5>
                        <small class="text-muted ">Genera las tablas y vistas necesarias para el control de accesos</small>
                    </a>
                    <a href="#" onclick="crearMenuPrincipal()" class="list-group-item list-group-item-action">    
                        <h5 class="mb-1  icon-flow-split">Crear Menú Principal</h5>
                        <small class="text-muted">Configura el menú de navegación principal usando el Modulo de Accesos</small>
                    </a>

                    <a href="#" onclick="crearPantallaLogin()" class="list-group-item list-group-item-action disabled" aria-disabled="true">    
                        <h5 class="mb-1 icon-login">Crear Pantalla de Login</h5>
                        <small class="text-muted">Genera la página de inicio de sesión</small>
                    </a>
                    
                    <a href="accesos/llenar_programas.php" class="list-group-item list-group-item-action">
                        <h5 class="mb-1">Llenar Programas</h5>
                        <small class="text-muted">Gestiona los programas y permisos del sistema</small>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script> 
    <script>
        // Verificar si existe la ruta al cargar la página
        $(document).ready(function() {
            <?php if (empty($ruta)): ?>
                alert('La ruta del proyecto es requerida. Por favor, configúrela en la pantalla principal.');
                window.location.href = 'index.php';
            <?php endif; ?>
        });

        function crearBaseDatos() {
            $.ajax({
                url: 'accesos/crear_base_datos.php',
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let mensaje = response.message;
                        if (response.warnings && response.warnings.length > 0) {
                            console.log("Avisos:", response.warnings);
                        }
                        alert('Éxito: ' + mensaje);
                    } else {
                        console.error("Error detallado:", response);
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error completo:", {
                        status: status,
                        error: error,
                        response: xhr.responseText,
                        xhr: xhr
                    });
                    alert('Error al procesar la solicitud. Revise la consola para más detalles.');
                }
            });
        }
        // funcion llama la creacion del menu principal
        function crearMenuPrincipal() {
            $.ajax({
                url: 'accesos/crea_menu_principal.php',
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let mensaje = response.message;
                        alert('Éxito: ' + mensaje);
                    } else {
                        console.error("Error detallado:", response);
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error completo:", {
                        status: status,
                        error: error,
                        response: xhr.responseText,
                        xhr: xhr
                    });
                    alert('Error al procesar la solicitud. Revise la consola para más detalles.');
                }
            });
        }

        // Función para crear la pantalla de login  crearPantallaLogin()
        function crearPantallaLogin() {
            $.ajax({
                url: 'accesos/crea_pantalla_login.php',
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let mensaje = response.message;
                        alert('Éxito: ' + mensaje);
                    } else {
                        console.error("Error detallado:", response);
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error completo:", {
                        status: status,
                        error: error,
                        response: xhr.responseText,
                        xhr: xhr
                    });
                    alert('Error al procesar la solicitud. Revise la consola para más detalles.');
                }
            });
        }

    </script>
</body>
</html>