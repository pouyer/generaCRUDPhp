<?php
session_start();

// Incluir archivos necesarios
require_once('include/funciones_utilidades.php');

// Inicializar variables
$mensaje = '';
$baseDatos = '';
$ruta = '';
$nombre_archivo = '';

// Capturar y guardar en sesión los valores de ruta y nombre_archivo cuando se ingresan
if(isset($_POST['ruta'])) {
    $_SESSION['ruta'] = $_POST['ruta'];
}
if(isset($_POST['nombre_archivo'])) {
    $_SESSION['nombre_archivo'] = $_POST['nombre_archivo'];
}

// Recuperar valores de la sesión
$ruta = isset($_SESSION['ruta']) ? $_SESSION['ruta'] : '';
$nombre_archivo = isset($_SESSION['nombre_archivo']) ? $_SESSION['nombre_archivo'] : '';

// Verificar si se ha enviado el formulario para generar el CRUD
if (isset($_POST['generar_crud'])) {
    // Obtener y validar los datos del formulario
    $baseDatos = isset($_POST['base_datos']) ? $_POST['base_datos'] : '';
    $ruta = isset($_POST['ruta']) ? normalizar_ruta($_POST['ruta']) : '';
    $nombre_archivo = isset($_POST['nombre_archivo']) ? $_POST['nombre_archivo'] : '';
    
    // Validar que los campos requeridos no estén vacíos
    if (empty($baseDatos) || empty($ruta) || empty($nombre_archivo)) {
        $mensaje = "Error: Todos los campos son requeridos";
    } else {
        // Validar la ruta
        $validacion = validar_ruta($ruta);
        if ($validacion !== true) {
            $mensaje = "Error: " . $validacion;
        } else {
            // Verificar si se seleccionaron tablas
            if (isset($_POST['tabla']) && is_array($_POST['tabla'])) {
                foreach ($_POST['tabla'] as $tabla) {
                    require_once('include/generar_crud.php');
                    $resultado = generar_crud_completo($tabla, $baseDatos, $ruta, $nombre_archivo);
                    
                    if ($resultado === true) {
                        $mensaje .= "CRUD generado exitosamente para la tabla: $tabla<br>";
                    } else {
                        $mensaje .= "Error al generar CRUD para la tabla $tabla: $resultado<br>";
                    }
                }
            } else {
                $mensaje = "No se han seleccionado tablas.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de CRUD</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1 class="text-center">Generador de CRUD</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Formulario para configuración -->
        <form action="" method="post">
            <div class="form-group">
                <label for="host">Host:</label>
                <input type="text" class="form-control" id="host" name="host" value="localhost">
            </div>
            
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" class="form-control" id="usuario" name="usuario" value="root">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>

            <div class="form-group">
                <label for="ruta">Ruta de destino:</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="ruta" name="ruta" 
                           value="<?php echo htmlspecialchars($ruta); ?>"
                           placeholder="Seleccione o escriba la carpeta de destino...">
                    <input type="text" class="form-control" id="nombre_archivo" name="nombre_archivo" 
                           value="<?php echo htmlspecialchars($nombre_archivo); ?>"
                           placeholder="Nombre del archivo de conexión">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-secondary" onclick="abrirExploradorCarpetas()">
                            Seleccionar Carpeta
                        </button>
                    </div>
                </div>
                <input type="file" id="fileInput" webkitdirectory directory multiple 
                       style="display:none;" onchange="setRuta()">
            </div>

            <!-- Selector de base de datos -->
            <div class="form-group">
                <label for="base_datos">Seleccione una base de datos:</label>
                <select name="base_datos" id="base_datos" class="form-control" required>
                    <option value="">Seleccione una base de datos...</option>
                    <?php include('include/lista_bases_datos.php'); ?>
                </select>
            </div>

            <div class="btn-group" role="group">
                <!-- Botón para generar archivo de conexión -->
                <button type="button" class="btn btn-secondary" onclick="generarConexion()">
                    Generar Archivo de Conexión
                </button>
                
                <!-- Botón para mostrar tablas -->
                <button type="submit" name="mostrar_tablas" class="btn btn-primary">
                    Mostrar Tablas
                </button>
            </div>
        </form>

        <!-- Formulario para seleccionar tablas -->
<?php 
if (isset($_POST['mostrar_tablas']) || isset($_POST['base_datos'])) {
    // Incluir archivo de conexión
    require_once('include/conexion.php');
    
    // Seleccionar la base de datos
    if ($conexion->select_db($_POST['base_datos'])) {
        // Obtener la lista de tablas
        $resultado = $conexion->query("SHOW TABLES");
        
        if ($resultado && $resultado->num_rows > 0) {
?>
            <form action="include/generar_crud.php" method="post" class="mt-4">
                <input type="hidden" name="base_datos" value="<?php echo htmlspecialchars($_POST['base_datos']); ?>">
                <input type="hidden" name="ruta" value="<?php echo htmlspecialchars($ruta); ?>">
                <input type="hidden" name="nombre_archivo" value="<?php echo htmlspecialchars($nombre_archivo); ?>">
                
                <h2>Tablas de la base de datos "<?php echo htmlspecialchars($_POST['base_datos']); ?>"</h2>
                
                <!-- Mostrar la ruta y archivo de conexión actuales -->
                <div class="mb-3">
                    <strong>Ruta:</strong> <?php echo htmlspecialchars($ruta); ?><br>
                    <strong>Archivo de conexión:</strong> <?php echo htmlspecialchars($nombre_archivo); ?>
                </div>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Seleccionar</th>
                            <th>Tabla</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($tabla = $resultado->fetch_array()) {
                            echo "<tr>";
                            echo "<td><input type='checkbox' name='tabla[]' value='" . 
                                 htmlspecialchars($tabla[0]) . "'></td>";
                            echo "<td>" . htmlspecialchars($tabla[0]) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <button type="submit" name="generar_crud" class="btn btn-primary" 
                        onclick="return validarSeleccion()">
                    Generar CRUD
                </button>
            </form>

            <script>
            function validarSeleccion() {
                var checkboxes = document.querySelectorAll('input[name="tabla[]"]:checked');
                if (checkboxes.length === 0) {
                    alert('Por favor, seleccione al menos una tabla');
                    return false;
                }
                return true;
            }
            </script>
<?php
        } else {
            echo "<div class='alert alert-warning'>No se encontraron tablas en la base de datos.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Error al conectar con la base de datos: " . 
             $conexion->error . "</div>";
    }
}
?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setRuta() {
            var fileInput = document.getElementById('fileInput');
            var rutaInput = document.getElementById('ruta');
            
            if (fileInput.files && fileInput.files[0]) {
                var ruta = fileInput.files[0].path;
                rutaInput.value = ruta.substring(0, ruta.lastIndexOf('\\'));
                fileInput.value = '';
                
                // Actualizar sesión via AJAX
                $.post('include/actualizar_sesion.php', {
                    ruta: rutaInput.value
                });
            }
        }

        function abrirExploradorCarpetas() {
            document.getElementById('fileInput').click();
        }

        function generarConexion() {
            var host = document.getElementById('host').value;
            var usuario = document.getElementById('usuario').value;
            var password = document.getElementById('password').value;
            var ruta = document.getElementById('ruta').value;
            var nombreArchivo = document.getElementById('nombre_archivo').value;
            var database = document.getElementById('base_datos').value;

            if (!nombreArchivo) {
                alert('Por favor, ingrese un nombre para el archivo de conexión');
                return;
            }

            var form = document.createElement('form');
            form.method = 'POST';
            form.action = 'include/generar_conexion.php';

            var campos = {
                'host': host,
                'usuario': usuario,
                'password': password,
                'ruta': ruta,
                'database': database,
                'nombre_archivo': nombreArchivo
            };

            for (var key in campos) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = campos[key];
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }

        // También actualizar la sesión cuando se cambia el nombre del archivo
        $(document).ready(function() {
            $('#nombre_archivo').change(function() {
                $.post('include/actualizar_sesion.php', {
                    nombre_archivo: $(this).val()
                });
            });
        });
    </script>
</body>
</html>