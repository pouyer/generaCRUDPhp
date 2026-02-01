<?php
session_start();
// Habilitar errores para diagnóstico de renderizado
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir archivos necesarios
require_once('include/funciones_utilidades.php');

// Inicializar variables
$mensaje = '';
$baseDatos = '';
$ruta = '';
$nombre_archivo = 'conexion.php';
$nombre_proyecto = '';

// Lógica para restablecer configuración a valores de .env
if (isset($_GET['reset_config'])) {
    unset($_SESSION['db_host']);
    unset($_SESSION['db_user']);
    unset($_SESSION['db_pass']);
    unset($_SESSION['db_port']);
    // Redirigir para limpiar la URL
    header('Location: index.php');
    exit;
}

// Capturar y guardar en sesión los valores de ruta, nombre_archivo y nombre_proyecto cuando se ingresan
if (isset($_POST['ruta'])) {
    $_SESSION['ruta'] = $_POST['ruta'];
}
if (isset($_POST['nombre_archivo'])) {
    $_SESSION['nombre_archivo'] = $_POST['nombre_archivo'];
}
if (isset($_POST['nombre_proyecto'])) {
    $_SESSION['nombre_proyecto'] = $_POST['nombre_proyecto'];
} elseif (!isset($_SESSION['nombre_proyecto'])) {
    $_SESSION['nombre_proyecto'] = ''; 
}

// Guardar configuración de conexión en sesión si se envía y no está vacía
if (!empty($_POST['host'])) $_SESSION['db_host'] = $_POST['host'];
if (!empty($_POST['usuario'])) $_SESSION['db_user'] = $_POST['usuario'];
if (!empty($_POST['password'])) $_SESSION['db_pass'] = $_POST['password'];
if (!empty($_POST['puerto'])) $_SESSION['db_port'] = $_POST['puerto'];

if (!empty($_POST['base_datos'])) {
    $_SESSION['base_datos'] = $_POST['base_datos'];
}

if (!empty($_POST['smtp_host'])) $_SESSION['smtp_host'] = $_POST['smtp_host'];
if (!empty($_POST['smtp_user'])) $_SESSION['smtp_user'] = $_POST['smtp_user'];
if (!empty($_POST['smtp_pass'])) $_SESSION['smtp_pass'] = $_POST['smtp_pass'];
if (!empty($_POST['smtp_port'])) $_SESSION['smtp_port'] = $_POST['smtp_port'];
if (!empty($_POST['smtp_from'])) $_SESSION['smtp_from'] = $_POST['smtp_from'];
if (!empty($_POST['admin_email'])) $_SESSION['admin_email'] = $_POST['admin_email'];
if (!empty($_POST['timezone'])) $_SESSION['timezone'] = $_POST['timezone'];

// Recuperar valores de la sesión
$ruta = isset($_SESSION['ruta']) ? $_SESSION['ruta'] : '';
$nombre_archivo = isset($_SESSION['nombre_archivo']) ? $_SESSION['nombre_archivo'] : 'conexion.php';
$nombre_proyecto = isset($_SESSION['nombre_proyecto']) ? $_SESSION['nombre_proyecto'] : ''; // Recuperar nombre_proyecto

// Verificar si se ha enviado el formulario para generar el CRUD
if (isset($_POST['generar_crud'])) {
    // Obtener y validar los datos del formulario
    $baseDatos = isset($_POST['base_datos']) ? $_POST['base_datos'] : '';
    $ruta = isset($_POST['ruta']) ? normalizar_ruta($_POST['ruta']) : '';
    $nombre_archivo = isset($_POST['nombre_archivo']) ? $_POST['nombre_archivo'] : '';
    $nombre_proyecto = isset($_POST['nombre_proyecto']) ? $_POST['nombre_proyecto'] : '';
    
    // Validar que los campos requeridos no estén vacíos
    if (empty($baseDatos) || empty($ruta) || empty($nombre_archivo)) {
        $mensaje = "Error: los campos  base_datos, ruta y nombre_archivo son requeridos";
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
    
    <?php include('headIconos.php'); // Incluir los elementos del encabezado iconos?>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Generador de CRUD</h1>       
        <div id="mensaje" class="alert alert-info alert-dismissible fade show" role="alert" style="display: none;">
            <span id="mensaje-text"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Formulario para configuración -->
        <form id="formConexion" method="post">
            <div class="row">
                <div class="col-md-3">
                    <label for="host">Host:</label>
                    <input type="text" class="form-control" id="host" name="host" placeholder="localhost" value="<?php echo isset($_SESSION['db_host']) ? htmlspecialchars($_SESSION['db_host']) : 'localhost'; ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="usuario">Usuario:</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" value="<?php echo isset($_SESSION['db_user']) ? htmlspecialchars($_SESSION['db_user']) : ''; ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="password">Contraseña:</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" value="<?php echo isset($_SESSION['db_pass']) ? htmlspecialchars($_SESSION['db_pass']) : ''; ?>">
                         <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', 'icon-pass-db')">
                                <i id="icon-pass-db" class="icon-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label for="puerto">Puerto:</label>
                    <input type="number" class="form-control" id="puerto" name="puerto" value="<?php echo isset($_SESSION['db_port']) ? htmlspecialchars($_SESSION['db_port']) : '3306'; ?>" placeholder="3306">
                </div>
                <div class="col-md-3">
                    <label for="admin_email">Email Administrador:</label>
                    <input type="email" class="form-control" id="admin_email" name="admin_email" placeholder="admin@ejemplo.com" value="<?php echo isset($_SESSION['admin_email']) ? htmlspecialchars($_SESSION['admin_email']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label for="timezone">Zona Horaria:</label>
                    <select class="form-control" id="timezone" name="timezone">
                        <?php
                        $timezones = DateTimeZone::listIdentifiers();
                        $saved_tz = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : 'America/Bogota';
                        foreach ($timezones as $tz) {
                            $selected = ($tz == $saved_tz) ? 'selected' : '';
                            echo "<option value=\"$tz\" $selected>$tz</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-12 text-right mt-2">
                    <a href="index.php?reset_config=1" class="btn btn-sm btn-outline-secondary" title="Borrar configuración manual y usar .env">
                        <i class="icon-ccw"></i> Usar valores por defecto (.env)
                    </a>
                </div>
                </div>


            <!-- Personalización Visual -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <h5>Personalización Visual</h5>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="logo_app">Logo Aplicación (Sup. Izq):</label>
                    <input type="file" class="form-control" id="logo_app" name="logo_app" accept=".png, .jpg, .jpeg, .webp">
                    <small class="form-text text-muted">Max-height sugerido: 50px</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="bg_login">Fondo Pantalla Login:</label>
                    <input type="file" class="form-control" id="bg_login" name="bg_login" accept=".png, .jpg, .jpeg, .webp">
                    <small class="form-text text-muted">Imagen grande para cubrir pantalla</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="favicon">Icono Navegador (Favicon):</label>
                    <input type="file" class="form-control" id="favicon" name="favicon" accept=".ico, .png">
                    <small class="form-text text-muted">Formato .ico o .png pequeño</small>
                </div>
            </div>

            <!-- Email Configuration -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <h5>Configuración de Correo (SMTP)</h5>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="smtp_host">SMTP Host:</label>
                    <input type="text" class="form-control" id="smtp_host" name="smtp_host" placeholder="smtp.gmail.com" value="<?php echo isset($_SESSION['smtp_host']) ? htmlspecialchars($_SESSION['smtp_host']) : ''; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="smtp_user">SMTP Usuario:</label>
                    <input type="text" class="form-control" id="smtp_user" name="smtp_user" placeholder="tucorreo@gmail.com" value="<?php echo isset($_SESSION['smtp_user']) ? htmlspecialchars($_SESSION['smtp_user']) : ''; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="smtp_pass">SMTP Contraseña:</label>
                     <div class="input-group">
                        <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" value="<?php echo isset($_SESSION['smtp_pass']) ? htmlspecialchars($_SESSION['smtp_pass']) : ''; ?>">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('smtp_pass', 'icon-pass-smtp')">
                                <i id="icon-pass-smtp" class="icon-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="smtp_port">SMTP Puerto:</label>
                    <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo isset($_SESSION['smtp_port']) ? htmlspecialchars($_SESSION['smtp_port']) : '587'; ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="smtp_from">Correo Remitente:</label>
                    <input type="email" class="form-control" id="smtp_from" name="smtp_from" placeholder="noreply@tuapp.com" value="<?php echo isset($_SESSION['smtp_from']) ? htmlspecialchars($_SESSION['smtp_from']) : ''; ?>">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-4">
                    <label for="nombre_proyecto">Nombre Proyecto:</label>
                    <input type="text" class="form-control" id="nombre_proyecto" name="nombre_proyecto" 
                           value="<?php echo htmlspecialchars($nombre_proyecto); ?>"> <!-- Mostrar valor de sesión -->
                </div>
      
            </div>

            <div class="form-group">
                <label for="ruta">Ruta del Proyecto:</label>
                <div class="input-group">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-secondary icon-folder" onclick="abrirExploradorCarpetas()">
                            Seleccionar Carpeta
                        </button>
                    </div>
                    <input type="text" class="form-control" id="ruta" name="ruta" 
                           value="<?php echo htmlspecialchars($ruta); ?>"
                           placeholder="Seleccione o escriba la carpeta de destino...">
                    <input type="hidden" id="nombre_archivo" name="nombre_archivo" value="conexion.php">
                </div>

            </div>

            <!-- Selector de base de datos -->
            <div class="form-group">
                <label for="base_datos">Seleccione una base de datos:</label>
                <div class="input-group mb-3">
                    <select name="base_datos" id="base_datos" class="form-control" required>
                        <option value="">Seleccione una base de datos...</option>
                        <?php include('include/lista_bases_datos.php'); ?>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-info icon-arrows-cw" type="submit" name="actualizar_db" title="Actualizar lista de bases de datos">
                             Conectar
                        </button>
                    </div>
                </div>
            </div>

            <div class="btn-group" role="group">
                <!-- Botón para generar archivo de conexión -->
            
                <button type="button" class="btn btn-secondary icon-link-2" onclick="generarConexion()">
                    Generar Archivo de Conexión
                </button>
                <div style="margin-top: 10px;"></div>
                <!-- Botón para mostrar tablas -->
                <button type="submit" name="mostrar_tablas" class="btn btn-primary icon-table">
                    Mostrar Tablas
                </button>
                <!-- Nuevo botón para módulo de acceso -->
                <button type="button" class="btn btn-success icon-expeditedssl" onclick="generarModuloAcceso()">
                    Generar Módulo de Acceso
                </button>
            </div>
        </form>

        <!-- Formulario para seleccionar tablas -->
<?php 
if (isset($_POST['mostrar_tablas']) || isset($_POST['actualizar_db']) || !empty($_POST['base_datos'])) {
    // Incluir archivo de conexión
    require_once('include/conexion.php');
    
    // Seleccionar la base de datos solo si no se ha seleccionado ya en conexion.php
    try {
        if (isset($_POST['base_datos']) && !empty($_POST['base_datos'])) {
            if (!$conexion->select_db($_POST['base_datos'])) {
                 $mensaje = "Error al seleccionar la base de datos: " . $conexion->error;
            }
        }
    } catch (Throwable $e) {
        $mensaje = "Error de conexión/selección: " . $e->getMessage();
        error_log("Error al seleccionar base de datos en index.php: " . $e->getMessage());
    }

    if (!empty($_POST['base_datos'])) { // Solo procedemos si la selección fue exitosa
        // Obtener la lista de tablas

        $sql = "SELECT TABLE_NAME , TABLE_COMMENT, TABLE_TYPE\n
                FROM information_schema.TABLES\n
                WHERE TABLE_SCHEMA = '".$_POST['base_datos']."'\n"
                . "ORDER BY TABLE_NAME ASC";
        try {
            $resultado = $conexion->query($sql);
            if (!$resultado) {
                throw new Exception($conexion->error);
            }
        } catch (Throwable $e) {
            $mensaje = "Error al listar tablas: " . $e->getMessage();
            $resultado = false;
        }
        
        if ($resultado && $resultado->num_rows > 0) {
?>
            <form id="formCRUD" method="post" class="mt-4">
                <input type="hidden" name="base_datos" value="<?php echo htmlspecialchars($_POST['base_datos']); ?>">
                <input type="hidden" name="ruta" value="<?php echo htmlspecialchars($ruta); ?>">
                <input type="hidden" name="nombre_archivo" value="<?php echo htmlspecialchars($nombre_archivo); ?>">
                <input type="hidden" name="nombre_proyecto" value="<?php echo htmlspecialchars($nombre_proyecto); ?>">

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
                            <th>Relaciones</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($tabla = $resultado->fetch_array()) {
                            $isView = ($tabla[2] === 'VIEW');
                            echo "<tr>";
                            echo "<td><input type='checkbox' name='tabla[]' value='" . 
                                 htmlspecialchars($tabla[0]) . "'></td>";
                            echo "<td>" . htmlspecialchars($tabla[0]) . "</td>";
                            echo "<td>";
                            if (!$isView) {
                                echo "<button type='button' class='btn btn-sm btn-info' onclick='configurarRelaciones(\"" . htmlspecialchars($tabla[0]) . "\")' title='Configurar Relaciones Inteligentes'>
                                        <i class='icon-flow-branch'></i> Configurar
                                      </button>";
                            } else {
                                echo "<span class='text-muted'>N/A (Vista)</span>";
                            }
                            echo "</td>";
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
    }
}
?>
    </div>

    <!-- Modal para Explorador de Carpetas -->
    <div class="modal fade" id="modalExplorador" tabindex="-1" role="dialog" aria-labelledby="modalExploradorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalExploradorLabel">Explorador de Carpetas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <input type="text" id="explorer_current_path" class="form-control" readonly>
                    </div>
                    <div id="explorer_list" class="list-group" style="max-height: 400px; overflow-y: auto;">
                        <!-- Los directorios se cargarán aquí -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="seleccionarCarpetaExplorer()">Seleccionar esta Carpeta</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Configurar Relaciones Inteligentes -->
    <div class="modal fade" id="modalRelaciones" tabindex="-1" role="dialog" aria-labelledby="modalRelacionesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRelacionesLabel">Relaciones para: <span id="rel_tabla_nombre"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="$('#modalRelaciones').modal('hide')"></button>
                </div>
                <div class="modal-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs mb-3" id="configTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="rel-tab" data-bs-toggle="tab" data-bs-target="#rel-pane" type="button" role="tab" aria-controls="rel-pane" aria-selected="true">Relaciones</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="layout-tab" data-bs-toggle="tab" data-bs-target="#layout-pane" type="button" role="tab" aria-controls="layout-pane" aria-selected="false">Layout Formulario</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="campos-tab" data-bs-toggle="tab" data-bs-target="#campos-pane" type="button" role="tab" aria-controls="campos-pane" aria-selected="false">Vistas y Exportación</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="apariencia-tab" data-bs-toggle="tab" data-bs-target="#apariencia-pane" type="button" role="tab" aria-controls="apariencia-pane" aria-selected="false">Apariencia</button>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content" id="configTabsContent">
                        <!-- Pestaña Relaciones -->
                        <div class="tab-pane fade show active" id="rel-pane" role="tabpanel" aria-labelledby="rel-tab">
                            <div id="relaciones_container">
                                <div class="text-center p-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Buscando relaciones...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pestaña Layout -->
                        <div class="tab-pane fade" id="layout-pane" role="tabpanel" aria-labelledby="layout-tab">
                            <div class="p-3">
                                <label for="num_columnas" class="form-label">Número de columnas en formularios (Crear/Editar):</label>
                                <select class="form-select" id="num_columnas">
                                    <option value="1">1 Columna</option>
                                    <option value="2" selected>2 Columnas (Por defecto)</option>
                                    <option value="3">3 Columnas</option>
                                    <option value="4">4 Columnas</option>
                                </select>
                                <div class="form-text mt-2">Define cómo se distribuirán los campos en la cuadrícula de los modales.</div>
                            </div>
                        </div>

                         <!-- Pestaña Campos -->
                         <div class="tab-pane fade" id="campos-pane" role="tabpanel" aria-labelledby="campos-tab">
                             <div class="p-3 border-bottom mb-2 bg-light rounded">
                                 <h6 class="fw-bold"><i class="icon-sort-name-up me-2"></i>Ordenamiento Predeterminado</h6>
                                 <div class="row g-2">
                                     <div class="col-md-4">
                                         <label class="form-label small">Prioridad 1</label>
                                         <div class="input-group input-group-sm">
                                             <select id="sort_field_1" class="form-select select-sort-field"><option value="">-- Ninguno --</option></select>
                                             <select id="sort_dir_1" class="form-select w-25"><option value="ASC">ASC</option><option value="DESC">DESC</option></select>
                                         </div>
                                     </div>
                                     <div class="col-md-4">
                                         <label class="form-label small">Prioridad 2</label>
                                         <div class="input-group input-group-sm">
                                             <select id="sort_field_2" class="form-select select-sort-field"><option value="">-- Ninguno --</option></select>
                                             <select id="sort_dir_2" class="form-select w-25"><option value="ASC">ASC</option><option value="DESC">DESC</option></select>
                                         </div>
                                     </div>
                                     <div class="col-md-4">
                                         <label class="form-label small">Prioridad 3</label>
                                         <div class="input-group input-group-sm">
                                             <select id="sort_field_3" class="form-select select-sort-field"><option value="">-- Ninguno --</option></select>
                                             <select id="sort_dir_3" class="form-select w-25"><option value="ASC">ASC</option><option value="DESC">DESC</option></select>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                             <div id="campos_config_container" class="p-2">
                                 <!-- Aquí se cargarán los campos con sus checkboxes -->
                                 <p class="text-muted text-center">Cargando campos...</p>
                             </div>
                         </div>

                        <!-- Pestaña Apariencia -->
                        <div class="tab-pane fade" id="apariencia-pane" role="tabpanel" aria-labelledby="apariencia-tab">
                            <div class="p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Tema Predeterminado</label>
                                        <select class="form-select" id="config_tema">
                                            <option value="azul">Azul Océano (Default)</option>
                                            <option value="verde">Verde Esmeralda</option>
                                            <option value="oscuro">Gris Oscuro Premium</option>
                                            <option value="purpura">Púrpura Real</option>
                                            <option value="custom">Personalizado...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Color Primario</label>
                                        <input type="color" class="form-control form-control-color w-100" id="config_color" value="#1e3c72" title="Elegir color primario">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Icono del Módulo</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light" id="preview_icono_btn"><i class="icon-dot-circled"></i></span>
                                            <input type="text" class="form-control" id="config_icono" placeholder="icon-table, fa-users, etc.">
                                            <button class="btn btn-outline-secondary" type="button" onclick="alert('Usa las clases de Fontello o FontAwesome')">?</button>
                                        </div>
                                        <div class="form-text">Este icono aparecerá en el encabezado de las páginas generadas.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="$('#modalRelaciones').modal('hide')">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarConfigRelaciones()">Guardar Configuración</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Lógica del Explorador de Carpetas
        function abrirExploradorCarpetas() {
            var rutaActual = $('#ruta').val();
            cargarDirectorio(rutaActual);
            var modalEl = document.getElementById('modalExplorador');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }

        function cargarDirectorio(ruta) {
            $('#explorer_list').html('<div class="list-group-item">Cargando...</div>');
            
            $.post('include/api_explorador.php', { ruta: ruta }, function(data) {
                if (data.error) {
                    $('#explorer_list').html('<div class="alert alert-danger">' + data.error + '</div>');
                    return;
                }

                $('#explorer_current_path').val(data.ruta_actual);
                $('#explorer_list').empty();

                data.directorios.forEach(function(dir) {
                    var icon = dir.tipo === 'padre' ? 'icon-up-open' : 'icon-folder'; // Asumiendo que imate fontello o similar, sino folder emoji 📁
                    var item = $('<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"></button>');
                    
                    // Contenido del item
                    item.html('<span>📁 ' + dir.nombre + '</span>');
                    
                    // Click para navegar
                    item.click(function() {
                        cargarDirectorio(dir.ruta);
                    });

                    $('#explorer_list').append(item);
                });
            }, 'json').fail(function() {
                $('#explorer_list').html('<div class="alert alert-danger">Error al conectar con el servidor.</div>');
            });
        }

        function seleccionarCarpetaExplorer() {
            var rutaSeleccionada = $('#explorer_current_path').val();
            $('#ruta').val(rutaSeleccionada);
            var modalEl = document.getElementById('modalExplorador');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            
            // Actualizar sesión
            $.post('include/actualizar_sesion.php', {
                ruta: rutaSeleccionada
            });
        }

        function generarConexion() {
            var form = document.getElementById('formConexion'); // Asegúrate que el form tenga este ID o usa un FormData temporal
            var formData = new FormData();

            // Agregar campos manuales si no usas el form submit directo
            formData.append('host', $('#host').val());
            formData.append('usuario', $('#usuario').val());
            formData.append('password', $('#password').val());
            formData.append('puerto', $('#puerto').val());
            formData.append('ruta', $('#ruta').val());
            formData.append('nombre_archivo', $('#nombre_archivo').val());
            formData.append('database', $('#base_datos').val());
            formData.append('smtp_host', $('#smtp_host').val());
            formData.append('smtp_user', $('#smtp_user').val());
            formData.append('smtp_pass', $('#smtp_pass').val());
            formData.append('smtp_port', $('#smtp_port').val());
            formData.append('smtp_from', $('#smtp_from').val());
            formData.append('admin_email', $('#admin_email').val());
            formData.append('timezone', $('#timezone').val());
            
            // Agregar archivos
            var logo = $('#logo_app')[0].files[0];
            var bg = $('#bg_login')[0].files[0];
            var favicon = $('#favicon')[0].files[0];

            if (logo) formData.append('logo_app', logo);
            if (bg) formData.append('bg_login', bg);
            if (favicon) formData.append('favicon', favicon);

            $.ajax({
                type: 'POST',
                url: 'include/generar_conexion.php',
                data: formData,
                dataType: 'json',
                processData: false, // Importante para FormData
                contentType: false, // Importante para FormData
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
            var tablasSeleccionadas = [];
            var tiposTabla = [];
            
            $('input[name="tabla[]"]:checked').each(function() {
                tablasSeleccionadas.push(this.value);
                tiposTabla.push($(this).closest('tr').find('td:eq(3)').text()); // Obtiene el TABLE_TYPE de la cuarta columna (índice 3)
            });

            if (tablasSeleccionadas.length === 0) {
                alert('Por favor, seleccione al menos una tabla.');
                return;
            }

            var ruta = $('#ruta').val();
            var nombre_archivo = $('#nombre_archivo').val();
            var base_datos = $('#base_datos').val();

            if (!ruta || !nombre_archivo || !base_datos) {
                alert('Por favor complete los campos requeridos:\n- Ruta\n- Archivo de conexión\n- Base de datos');
                return;
            }

            var formData = {
                tabla: tablasSeleccionadas,
                tipo_tabla: tiposTabla,
                base_datos: base_datos,
                ruta: ruta,
                nombre_archivo: nombre_archivo,
                config_tablas: JSON.stringify(tablasConfig) // Enviar configuración extendida
            };

            console.log(formData); // Para depuración

            $.ajax({
                type: 'POST',
                url: 'include/generar_crud.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#mensaje-text').empty();
                        response.messages.forEach(function(message) {
                            $('#mensaje-text').append(message + '<br>');
                        });
                        $('#mensaje').removeClass('alert-danger').addClass('alert-success').show();
                    } else {
                        $('#mensaje-text').text(response.message);
                        $('#mensaje').removeClass('alert-success').addClass('alert-danger').show();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error:", error); // Log del error en la consola
                    $('#mensaje-text').text('Error al procesar la solicitud: ' + error);
                    $('#mensaje').removeClass('alert-success').addClass('alert-danger').show();
                }
            });
        }

        // --- Lógica de Configuración de Tablas ---
        var tablasConfig = <?php 
            $config_base = isset($_SESSION['config_tablas']) ? $_SESSION['config_tablas'] : '{}';
            // Validar que sea un JSON válido o resetear
            if (!empty($config_base) && is_string($config_base)) {
                json_decode($config_base);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $config_base = '{}';
                }
            } else {
                $config_base = '{}';
            }
            echo $config_base; 
        ?>; // Objeto global persistido en sesión

        function configurarRelaciones(tabla) {
            $('#rel_tabla_nombre').text(tabla);
            $('#relaciones_container').html('<div class="text-center p-3"><div class="spinner-border text-primary"></div><p>Buscando configuración...</p></div>');
            $('#campos_config_container').html('<p class="text-muted text-center">Cargando campos...</p>');
            
            var modalEl = document.getElementById('modalRelaciones');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            var base_datos = $('#base_datos').val();
            
            // Intentar cargar configuración desde BD primero
            $.post('include/actualizar_sesion.php', { 
                accion: 'cargar_config', 
                tabla: tabla,
                base_datos: base_datos 
            }, function(dbConfig) {
                if (dbConfig.error) {
                    console.warn("Aviso al cargar config:", dbConfig.error);
                }
                
                var configExistente = (dbConfig && typeof dbConfig === 'object' && !dbConfig.error) ? dbConfig : (tablasConfig[tabla] || { relaciones: {}, columns: 2, fields: {} });
                
                // Sincronizar con el objeto global
                tablasConfig[tabla] = configExistente;
                
                $.post('include/obtener_relaciones.php', { base_datos: base_datos, tabla: tabla }, function(response) {
                    if (!response.success) {
                        $('#relaciones_container').html('<div class="alert alert-danger">' + response.message + '</div>');
                        return;
                    }
                    
                    // Poblar selectores de ordenamiento
                    $('.select-sort-field').empty().append('<option value="">-- Ninguno --</option>');
                    if (response.columnas_tabla) {
                        response.columnas_tabla.forEach(function(col) {
                            $('.select-sort-field').append('<option value="' + col + '">' + col + '</option>');
                        });
                    }
                    
                    $('.select-sort-field').val('');
                    $('#sort_dir_1, #sort_dir_2, #sort_dir_3').val('ASC');

                    if (configExistente.sort && Array.isArray(configExistente.sort)) {
                        configExistente.sort.forEach(function(s, idx) {
                            var n = idx + 1;
                            if(n <= 3) {
                                $('#sort_field_' + n).val(s.field);
                                $('#sort_dir_' + n).val(s.dir);
                            }
                        });
                    }

                    // Apariencia
                    $('#config_tema').val(configExistente.tema || 'azul');
                    $('#config_color').val(configExistente.color || '#1e3c72');
                    $('#config_icono').val(configExistente.icono || 'icon-table');
                    $('#preview_icono_btn i').attr('class', configExistente.icono || 'icon-dot-circled');

                    // Relaciones
                    if (!response.relaciones || response.relaciones.length === 0) {
                        $('#relaciones_container').html('<div class="alert alert-info">No se detectaron llaves foráneas para esta tabla.</div>');
                    } else {
                        var htmlRel = '<div class="table-responsive"><table class="table table-sm">';
                        htmlRel += '<thead><tr><th>Campo Local</th><th>Tabla Relacionada</th><th>Mostrar</th><th>Ordenar por</th><th>Filtro SQL</th></tr></thead><tbody>';

                        response.relaciones.forEach(function(rel) {
                            var relConf = configExistente.relaciones ? configExistente.relaciones[rel.campo_local] : null;
                            var currentDisplay = relConf ? relConf.display : '';
                            var currentSort = relConf ? relConf.sort_by : '';
                            var currentWhere = relConf ? (relConf.where || '') : '';
                            
                            htmlRel += '<tr><td><code>' + rel.campo_local + '</code></td><td><code>' + rel.tabla_padre + '</code></td><td>';
                            htmlRel += '<select class="form-select select-rel-display" data-campo="' + rel.campo_local + '" data-parent="' + rel.tabla_padre + '" data-parentid="' + rel.campo_padre + '" data-nullable="' + rel.es_nullable + '">';
                            htmlRel += '<option value="">-- No usar relación --</option>';
                            rel.columnas_padre.forEach(function(col) {
                                var selected = (col === currentDisplay) ? 'selected' : '';
                                htmlRel += '<option value="' + col + '" ' + selected + '>' + col + '</option>';
                            });
                            htmlRel += '</select></td>';
                            
                            htmlRel += '<td><select class="form-select select-rel-sort" data-campo="' + rel.campo_local + '">';
                            htmlRel += '<option value="">(Auto: Campo Mostrar)</option>';
                            rel.columnas_padre.forEach(function(col) {
                                var selectedS = (col === currentSort) ? 'selected' : '';
                                htmlRel += '<option value="' + col + '" ' + selectedS + '>' + col + '</option>';
                            });
                            htmlRel += '</select></td>';
                            
                            htmlRel += '<td><input type="text" class="form-control form-select-sm input-rel-where" data-campo="' + rel.campo_local + '" value="' + currentWhere + '" placeholder="ej: estado = \'activo\'"></td></tr>';
                        });
                        htmlRel += '</tbody></table></div>';
                        $('#relaciones_container').html(htmlRel);
                    }

                    // Layout
                    $('#num_columnas').val(configExistente.columns || 2);

                    // Campos
                    var htmlCampos = '<div class="table-responsive"><table class="table table-sm table-striped" id="tabla-campos-config">';
                    htmlCampos += '<thead><tr><th>Orden</th><th>Campo</th><th class="text-center">Listado</th><th class="text-center">Exportar</th><th class="text-center">Auditoría</th></tr></thead><tbody class="sortable-tbody">';

                    var cols = [...(response.columnas_tabla || [])];
                    cols.sort(function(a, b) {
                        var orderA = (configExistente.fields && configExistente.fields[a] && configExistente.fields[a].order !== undefined) ? configExistente.fields[a].order : 999;
                        var orderB = (configExistente.fields && configExistente.fields[b] && configExistente.fields[b].order !== undefined) ? configExistente.fields[b].order : 999;
                        return orderA - orderB;
                    });

                    cols.forEach(function(col) {
                        var fieldConf = configExistente.fields ? configExistente.fields[col] : null;
                        var showInList = (fieldConf && fieldConf.list !== undefined) ? fieldConf.list : true;
                        var showInExport = (fieldConf && fieldConf.export !== undefined) ? fieldConf.export : true;
                        var auditConfig = (fieldConf && fieldConf.audit) ? fieldConf.audit : '';
                        
                        htmlCampos += '<tr class="campo-row" data-campo="' + col + '">';
                        htmlCampos += '<td>';
                        htmlCampos += '<button type="button" class="btn btn-xs btn-outline-secondary py-0" onclick="moverFila(this, \'up\')">▲</button>';
                        htmlCampos += '<button type="button" class="btn btn-xs btn-outline-secondary py-0" onclick="moverFila(this, \'down\')">▼</button>';
                        htmlCampos += '</td>';
                        htmlCampos += '<td>' + col + '</td>';
                        htmlCampos += '<td class="text-center"><input type="checkbox" class="check-list" data-campo="' + col + '" ' + (showInList ? 'checked' : '') + '></td>';
                        htmlCampos += '<td class="text-center"><input type="checkbox" class="check-export" data-campo="' + col + '" ' + (showInExport ? 'checked' : '') + '></td>';
                        htmlCampos += '<td><select class="form-select form-select-sm select-audit">';
                        htmlCampos += '<option value="">Ninguno</option>';
                        htmlCampos += '<option value="insert" ' + (auditConfig === 'insert' ? 'selected' : '') + '>Usuario Inserta</option>';
                        htmlCampos += '<option value="update" ' + (auditConfig === 'update' ? 'selected' : '') + '>Usuario Modifica</option>';
                        htmlCampos += '</select></td>';
                        htmlCampos += '</tr>';
                    });
                    htmlCampos += '</tbody></table></div>';
                    $('#campos_config_container').html(htmlCampos);

                }, 'json').fail(function(xhr) {
                    console.error("Error obtener_relaciones:", xhr.responseText);
                    $('#relaciones_container').html('<div class="alert alert-danger">Error al obtener columnas y relaciones.</div>');
                });
            }, 'json').fail(function(xhr) {
                console.error("Error cargar_config:", xhr.responseText);
                var errorMsg = "Error al cargar la configuración persistente desde el servidor.";
                if (xhr.responseJSON && xhr.responseJSON.error) errorMsg += "<br>Detalle: " + xhr.responseJSON.error;
                $('#relaciones_container').html('<div class="alert alert-danger">' + errorMsg + '</div>');
            });
        }

        function guardarConfigRelaciones() {
            var tabla = $('#rel_tabla_nombre').text();
            var config = {
                relaciones: {},
                columns: $('#num_columnas').val(),
                fields: {},
                sort: [],
                tema: $('#config_tema').val(),
                color: $('#config_color').val(),
                icono: $('#config_icono').val()
            };

            // Capturar Ordenamiento Predeterminado (Triple)
            for(var i=1; i<=3; i++) {
                var f = $('#sort_field_'+i).val();
                if(f) {
                    config.sort.push({ field: f, dir: $('#sort_dir_'+i).val() });
                }
            }

            // Capturar Relaciones
            $('.select-rel-display').each(function() {
                var field = $(this).data('campo');
                var display = $(this).val();
                var sortBy = $('.select-rel-sort[data-campo="' + field + '"]').val();
                var where = $('.input-rel-where[data-campo="' + field + '"]').val();
                if (display) {
                    config.relaciones[field] = {
                        display: display,
                        sort_by: sortBy,
                        where: where,
                        parent: $(this).data('parent'),
                        parentid: $(this).data('parentid'),
                        nullable: $(this).data('nullable')
                    };
                }
            });

            // Capturar Campos (Listado, Export y Orden)
            var orderCounter = 0;
            $('.campo-row').each(function() {
                var campo = $(this).data('campo');
                if (!config.fields[campo]) config.fields[campo] = {};
                config.fields[campo].list = $(this).find('.check-list').is(':checked');
                config.fields[campo].export = $(this).find('.check-export').is(':checked');
                config.fields[campo].audit = $(this).find('.select-audit').val();
                config.fields[campo].order = orderCounter++;
            });

            tablasConfig[tabla] = config;
            console.log("Configuración guardada para " + tabla + ":", config);
            
            // Actualizar sesión con la nueva configuración
            $.post('include/actualizar_sesion.php', {
                config_tablas: JSON.stringify(tablasConfig)
            });

            $('#modalRelaciones').modal('hide');
            // Opcional: mostrar un indicador visual en la tabla de que esta tabla tiene config especial
        }

        function moverFila(btn, direccion) {
            var row = $(btn).closest('tr');
            if (direccion === 'up') {
                row.insertBefore(row.prev());
            } else {
                row.insertAfter(row.next());
            }
        }
        $(document).ready(function() {
            $('#nombre_archivo').change(function() {
                $.post('include/actualizar_sesion.php', {
                    nombre_archivo: $(this).val()
                });
            });
        });

        // función para generar el módulo de acceso
        function generarModuloAcceso() {
            var ruta = $('#ruta').val();
            var nombre_archivo = $('#nombre_archivo').val();
            var base_datos = $('#base_datos').val();
            var nombre_proyecto = $('#nombre_proyecto').val(); // Capturar nombre_proyecto

            console.log("Ruta:", ruta);
            console.log("Nombre de archivo:", nombre_archivo);
            console.log("Base de datos:", base_datos);
            console.log("Nombre del proyecto:", nombre_proyecto);

            
            // Verificar que la ruta, el archivo y la base de datos no estén vacíos
            if (!ruta || !nombre_archivo || !base_datos ) { 
                alert('Por favor, complete los siguientes campos:\n' +
          'Ruta: ' + (ruta || 'No especificada') + '\n' +
          'Nombre de archivo: ' + (nombre_archivo || 'No especificado') + '\n' +
          'Base de datos: ' + (base_datos || 'No especificada')+ '\n' +
          'Nombre del proyecto: ' + (nombre_proyecto || 'No especificado'));
                return;
            }

            // Actualizar la sesión y redirigir
            $.post('include/actualizar_sesion.php', {
                ruta: ruta,
                nombre_archivo: nombre_archivo,
                base_datos: base_datos,
                nombre_proyecto: nombre_proyecto, // Enviar nombre_proyecto
                admin_email: $('#admin_email').val() // Enviar email administrador
            }, function(response) {
                console.log("Sesión actualizada:", response); // Depuración
                window.location.href = 'modulo_acceso.php';
            }).fail(function(xhr) {
                console.error("Error al actualizar la sesión:", xhr.responseText); // Depuración
                alert('Error al actualizar la sesión.');
            });
        }

        function togglePassword(inputId, iconId) {
            var input = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
            
            if (input.type === "password") {
                input.type = "text";
                // Intentar cambiar el icono si existe una versión 'off' o tachada, si no, solo cambiar color o dejarlo
                // Asumiendo que icon-eye es el base, tal vez icon-eye-off existe. 
                // Por ahora solo cambiamos el estado del input.
                icon.classList.add('text-primary'); // Highlight when visible
            } else {
                input.type = "password";
                icon.classList.remove('text-primary');
            }
        }
        // Cargar Bootstrap JS al final
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>