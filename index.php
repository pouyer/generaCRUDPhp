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
if (isset($_POST['ruta'])) {
    $_SESSION['ruta'] = $_POST['ruta'];
}
if (isset($_POST['nombre_archivo'])) {
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
                // Aquí solo se establece un mensaje temporal
                $mensaje = "Generando CRUD..."; // Mensaje temporal
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
        
        <div id="mensaje" class="alert alert-info alert-dismissible fade show" role="alert" style="display: none;">
            <span id="mensaje-text"></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Formulario para configuración -->
        <form id="formConexion" method="post">
            <div class="row">
                <div class="col-md-4">
                    <label for="host">Host:</label>
                    <input type="text" class="form-control" id="host" name="host" >
                </div>
                
                <div class="col-md-4">
                    <label for="usuario">Usuario:</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" >
                </div>
                
                <div class="col-md-4">
                    <label for="password">Contraseña:</label>
                    <input type="password" class="form-control" id="password" name="password" >
                </div>
            </div>
            <div class="form-group">
                <label for="ruta">Ruta del Proyecto:</label>
                <div class="input-group">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-secondary" onclick="abrirExploradorCarpetas()">
                            Seleccionar Carpeta
                        </button>
                    </div>
                    <input type="text" class="form-control" id="ruta" name="ruta" 
                           value="<?php echo htmlspecialchars($ruta); ?>"
                           placeholder="Seleccione o escriba la carpeta de destino...">
                    <input type="text" class="form-control" id="nombre_archivo" name="nombre_archivo" 
                           value="<?php echo htmlspecialchars($nombre_archivo); ?>"
                           placeholder="Nombre del archivo de conexión">
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
                <div style="margin-top: 10px;"></div>
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
        $sql = "SELECT TABLE_NAME , TABLE_COMMENT, TABLE_TYPE\n
                FROM information_schema.TABLES\n
                WHERE TABLE_SCHEMA = '".$_POST['base_datos']."'\n"
                . "ORDER BY TABLE_NAME ASC";
        $resultado = $conexion->query($sql);
        
        if ($resultado && $resultado->num_rows > 0) {
?>
            <form id="formCRUD" method="post" class="mt-4">
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
                            <th>Tipo</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($tabla = $resultado->fetch_array()) {
                            echo "<tr>";
                            echo "<td><input type='checkbox' name='tabla[]' value='" . 
                                 htmlspecialchars($tabla[0]) . "'></td>";
                            echo "<td>" . htmlspecialchars($tabla[0]) . "</td>";
                            echo "<td>" . htmlspecialchars($tabla[2]) . "</td>";
                            echo "<td>" . htmlspecialchars($tabla[1]) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <button type="button" name="generar_crud" class="btn btn-primary" onclick="generarCRUD()">
                    Generar CRUD
                </button>
            </form>
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
            var formData = {
                host: $('#host').val(),
                usuario: $('#usuario').val(),
                password: $('#password').val(),
                ruta: $('#ruta').val(),
                nombre_archivo: $('#nombre_archivo').val(),
                database: $('#base_datos').val()
            };

            $.ajax({
                type: 'POST',
                url: 'include/generar_conexion.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#mensaje-text').text(response.message);
                    $('#mensaje').removeClass('alert-info').addClass(response.success ? 'alert-success' : 'alert-danger').show();
                },
                error: function() {
                    $('#mensaje-text').text('Error al procesar la solicitud.');
                    $('#mensaje').removeClass('alert-info').addClass('alert-danger').show();
                }
            });
        }

        function validarSeleccion() {
                var checkboxes = document.querySelectorAll('input[name="tabla[]"]:checked');
                if (checkboxes.length === 0) {
                    alert('Por favor, seleccione al menos una tabla');
                    return false;
                }
                return true;
            }
            
        // Función para generar el CRUD
        function generarCRUD() {
            var tablasSeleccionadas = $('input[name="tabla[]"]:checked').map(function() {
                return this.value;
            }).get();

            if (tablasSeleccionadas.length === 0) {
                alert('Por favor, seleccione al menos una tabla.');
                return;
            }

            var formData = {
                tabla: tablasSeleccionadas,
                base_datos: $('#base_datos').val(),
                ruta: $('#ruta').val(),
                nombre_archivo: $('#nombre_archivo').val()
            };

            console.log(formData); // Agrega esta línea para depurar

            $.ajax({
                type: 'POST',
                url: 'include/generar_crud.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    // Limpiar el texto del mensaje antes de agregar nuevos mensajes
                    $('#mensaje-text').empty();

                    // Iterar sobre los mensajes y agregarlos al texto del mensaje
                    response.messages.forEach(function(message) {
                        $('#mensaje-text').append(message + '<br>'); // Agregar cada mensaje en una nueva línea
                    });

                    $('#mensaje').removeClass('alert-info').addClass(response.success ? 'alert-success' : 'alert-danger').show();
                },
                error: function(xhr, status, error) {
                    console.error("Error:", error); // Log del error en la consola
                    $('#mensaje-text').text('Error al procesar la solicitud: ' + error);
                    $('#mensaje').removeClass('alert-info').addClass('alert-danger').show();
                }
            });
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