<?php
session_start();
header('Content-Type: application/json');
include "../include/funciones_utilidades.php";

// Modifica el Login para que valide coneccionsi el usuario esta conectado
function ModificaIndex($ruta) {

    $ruta = normalizar_ruta($ruta);
    // Crear el archivo index.php
    $rutaPrincipalProyecto = rtrim($ruta, '/') . '/index.php';
    $contenido = "<?php
    session_start();

    // Verificar si el usuario está autenticado
    if (!isset(\$_SESSION['autenticado']) || \$_SESSION['autenticado'] !== true) {
        // Si no está autenticado, redirigir al login
        header('Location: accesos/vistas/vista_login.php');
        exit();
    }

    // Verificar si se requiere cambio de contraseña obligatorio
    if (isset(\$_SESSION['cambio_clave_obligatorio']) && \$_SESSION['cambio_clave_obligatorio'] === true) {
        // Si se requiere cambio de contraseña, redirigir a la página de cambio
        header('Location: accesos/vistas/vista_cambiar_password.php');
        exit();
    }

    // Si el usuario está autenticado y no requiere cambio de contraseña, redirigir al menú principal
    header('Location: accesos/vistas/vista_menu_principal.php');
    exit();
        
    ";
    // Crear el archivo de index.php para el modulo de accesos
    crearArchivo($rutaPrincipalProyecto, $contenido);
}    

// Modifica el archivo verificar_sesion.php para que verifique si el usuario ya esta conectado y lo redirija a la pantalla de inicio
function ModificaVerificarSesion($directorio) {
    // crea el archivo de verificacion de sesion
    $creaverificasesion = "$directorio/accesos/verificar_sesion.php";
    $contenido = "<?php
    session_start();

    // Determinar la ruta base del sitio
    \$ruta_base = '';
    \$script_name = \$_SERVER['SCRIPT_NAME'];
    \$document_root = \$_SERVER['DOCUMENT_ROOT'];

    // Convertir a formato de ruta del sistema
    \$script_path = str_replace('/', DIRECTORY_SEPARATOR, \$script_name);
    \$document_root = str_replace('/', DIRECTORY_SEPARATOR, \$document_root);

    // Obtener la carpeta base del proyecto
    \$base_dir = str_replace(\$document_root, '', dirname(\$script_path));
    \$project_directory = substr(\$base_dir, 0, strpos(\$base_dir, DIRECTORY_SEPARATOR, 1) ?: strlen(\$base_dir));

    // Determinar la profundidad de la carpeta actual
    \$current_script = \$_SERVER['SCRIPT_FILENAME'];
    \$accesos_path = \$document_root . \$project_directory . DIRECTORY_SEPARATOR . 'accesos';
    \$depth = substr_count(str_replace(\$accesos_path, '', dirname(\$current_script)), DIRECTORY_SEPARATOR) + 1;

    // Construir la ruta relativa según la profundidad
    \$ruta_relativa = '';
    for (\$i = 0; \$i < \$depth; \$i++) {
        \$ruta_relativa .= '../';
    }

    // Definir rutas para las vistas
    \$ruta_login = \$ruta_relativa . 'accesos/vistas/vista_login.php';
    \$ruta_cambiar_password = \$ruta_relativa . 'accesos/vistas/vista_cambiar_password.php';

    // Verificar si el usuario está autenticado
    if (!isset(\$_SESSION['usuario_id'])) {
        header('Location: \$ruta_login');
        exit;
    }

    // Si el usuario está autenticado, verificar si necesita cambiar la contraseña
    if (isset(\$_SESSION['cambio_clave_obligatorio']) && \$_SESSION['cambio_clave_obligatorio'] === 'S') {
        if (basename(\$_SERVER['PHP_SELF']) !== 'vista_cambiar_password.php') {
            header('Location: \$ruta_cambiar_password');
            exit;
        }
    }

    // Obtener información del usuario para uso en las páginas
    // Usando operador de fusión de null (??) o verificando con isset para evitar avisos
    \$usuario_id = \$_SESSION['usuario_id'] ?? 0;
    \$usuario_nombre = \$_SESSION['usuario_nombre'] ?? '';
    \$usuario_perfil = \$_SESSION['usuario_perfil'] ?? '';

    // Otras variables de sesión según sea necesario
    ?>  ";
    crearArchivo($creaverificasesion, $contenido);
}
// crea pantalla de vista_login.php
function crearVistaLogin($directorio) {
    $archivoVistaLogin = "$directorio/accesos/vistas/vista_login.php";
    $contenido = "<?php
session_start();

// Verificar si el usuario ya está autenticado
if (isset(\$_SESSION['autenticado']) && \$_SESSION['autenticado'] === true) {
    // Si está autenticado pero tiene cambio obligatorio de clave, redirigir a cambiar contraseña
    if (isset(\$_SESSION['cambio_clave_obligatorio']) && \$_SESSION['cambio_clave_obligatorio'] === true) {
        header('Location: vista_cambiar_password.php');
        exit();
    }
    // Si está autenticado y no tiene cambio obligatorio, redirigir al índice
    header('Location: ../../index.php');
    exit();
}

// Obtener mensajes de sesión
\$login_error = \$_SESSION['login_error'] ?? null;
\$registro_exito = \$_SESSION['registro_exito'] ?? null;
\$restablecer_exito = \$_SESSION['restablecer_exito'] ?? null;

// Limpiar mensajes de sesión una vez utilizados
unset(\$_SESSION['login_error']);
unset(\$_SESSION['registro_exito']);
unset(\$_SESSION['restablecer_exito']);
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Iniciar Sesión</title>
    <?php include('../headIconos.php'); ?>
    <link rel='stylesheet' href='../css/estilos.css'>
</head>
<body>
    <div class='container'>
        <div class='login-container'>
            <h2 class='text-center mb-4'>Iniciar Sesión</h2>
            
            <?php if (\$login_error): ?>
                <div class='alert alert-danger'><?php echo \$login_error; ?></div>
            <?php endif; ?>
            
            <?php if (\$registro_exito): ?>
                <div class='alert alert-success'><?php echo \$registro_exito; ?></div>
            <?php endif; ?>
            
            <?php if (\$restablecer_exito): ?>
                <div class='alert alert-success'><?php echo \$restablecer_exito; ?></div>
                <?php if (isset(\$_SESSION['debug_nueva_clave'])): ?>
                    <div class='alert alert-info'>
                        <p><strong>Información de desarrollo:</strong> Se generó la siguiente contraseña:</p>
                        <p>Usuario: <?php echo \$_SESSION['debug_nueva_clave']['username']; ?></p>
                        <p>Nueva clave: <?php echo \$_SESSION['debug_nueva_clave']['nueva_clave']; ?></p>
                    </div>
                    <?php unset(\$_SESSION['debug_nueva_clave']); ?>
                <?php endif; ?>
            <?php endif; ?>
            
            <form action='../controladores/controlador_login.php?action=login' method='POST'>
                <div class='form-group'>
                    <label for='username'>Usuario:</label>
                    <input type='text' class='form-control' id='username' name='username' required>
                </div>
                <div class='form-group'>
                    <label for='password'>Contraseña:</label>
                    <input type='password' class='form-control' id='password' name='password' required>
                </div>
                <button type='submit' class='btn btn-primary btn-block'>Iniciar Sesión</button>
            </form>
            
            <div class='text-center mt-3'>
                <a href='vista_restablecer_password.php'>¿Olvidaste tu contraseña?</a>
            </div>
            
            <div class='text-center mt-3'>
                <p>¿No tienes una cuenta? <a href='vista_registro.php'>Regístrate aquí</a></p>
            </div>
        </div>
    </div>
        <script src='https://code.jquery.com/jquery-3.7.1.min.js'></script>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html> 
    ";
    crearArchivo($archivoVistaLogin, $contenido);
}    

//crea controlador_login.php
function crearControladorLogin($directorio) {
    $archivoControladorLogin = "$directorio/accesos/controladores/controlador_login.php";
    $contenido = "<?php
session_start();
require_once '../modelos/modelo_acc_usuario.php';

class ControladorLogin {
    private \$modelo;

    public function __construct() {
        \$this->modelo = new ModeloAcc_usuario();
    }

    public function iniciarSesion(\$username, \$password) {
        \$usuario = \$this->modelo->verificarCredenciales(\$username, \$password);
        
        if (\$usuario) {
            // Iniciar sesión
            \$_SESSION['usuario_id'] = \$usuario['id_usuario'];
            \$_SESSION['usuario_nombre'] = \$usuario['fullname'];
            \$_SESSION['usuario_username'] = \$usuario['username'];
            \$_SESSION['autenticado'] = true;
            
            // Obtener perfil del usuario (roles)
            \$roles = \$this->modelo->obtenerRolesPorUsuario(\$usuario['id_usuario']);
            \$_SESSION['usuario_perfil'] = !empty(\$roles) ? \$roles[0]['nombre_rol'] : 'Sin perfil';
            \$_SESSION['usuario_roles'] = \$roles;
            
            // Verificar si debe cambiar la contraseña obligatoriamente
            if (\$this->modelo->requiereCambioPassword(\$usuario['id_usuario'])) {
                \$_SESSION['cambio_clave_obligatorio'] = 'S';
                return 'cambio_clave';
            } else {
                \$_SESSION['cambio_clave_obligatorio'] = 'N';
            }
            
            return true;
        }
        
        return false;
    }
    
    public function cerrarSesion() {
        // Eliminar todas las variables de sesión
        \$_SESSION = array();
        
        // Destruir la sesión
        session_destroy();
        
        return true;
    }
    
    public function restablecerPassword(\$usuario_o_correo) {
        \$resultado = \$this->modelo->restablecerPassword(\$usuario_o_correo);
        
        if (\$resultado) {
            // Aquí se enviaría el correo (en una implementación real)
            // Por ahora solo retornamos los datos para mostrarlos en la vista
            return \$resultado;
        }
        
        return false;
    }
    
    public function cambiarPassword(\$id_usuario, \$password_actual, \$nueva_password, \$confirmar_password) {
        // Obtener usuario para verificar la contraseña actual
        \$usuario = \$this->modelo->obtenerPorId(\$id_usuario);
        
        if (!\$usuario || \$usuario['password'] !== \$password_actual) {
            return ['exito' => false, 'mensaje' => 'La contraseña actual es incorrecta'];
        }
        
        if (\$nueva_password !== \$confirmar_password) {
            return ['exito' => false, 'mensaje' => 'Las contraseñas nuevas no coinciden'];
        }
        
        // Cambiar la contraseña
        \$resultado = \$this->modelo->cambiarPassword(\$id_usuario, \$nueva_password);
        
        if (\$resultado) {
            return ['exito' => true, 'mensaje' => 'Contraseña actualizada correctamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar la contraseña'];
        }
    }
    
    public function registrarUsuario(\$datos) {
        // Verificar si el usuario ya existe
        \$usuario_existente = \$this->modelo->obtenerPorUsername(\$datos['username']);
        if (\$usuario_existente) {
            return ['exito' => false, 'mensaje' => 'El nombre de usuario ya está en uso'];
        }
        
        // Verificar si el correo ya existe
        if (!empty(\$datos['correo'])) {
            \$correo_existente = \$this->modelo->obtenerPorCorreo(\$datos['correo']);
            if (\$correo_existente) {
                return ['exito' => false, 'mensaje' => 'El correo electrónico ya está registrado'];
            }
        }
        
        // Registrar el nuevo usuario
        try {
            \$resultado = \$this->modelo->crear(\$datos);
            if (\$resultado) {
                return ['exito' => true, 'mensaje' => 'Usuario registrado correctamente'];
            } else {
                return ['exito' => false, 'mensaje' => 'Error al registrar el usuario'];
            }
        } catch (Exception \$e) {
            return ['exito' => false, 'mensaje' => 'Error: ' . \$e->getMessage()];
        }
    }
}

// Manejar las solicitudes
if (isset(\$_GET['action'])) {
    \$controlador = new ControladorLogin();
    \$action = \$_GET['action'];
    
    switch (\$action) {
        case 'login':
            if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
                \$username = \$_POST['username'] ?? '';
                \$password = \$_POST['password'] ?? '';
                
                \$resultado = \$controlador->iniciarSesion(\$username, \$password);
                
                if (\$resultado === true) {
                    // Redireccionar al índice
                    header('Location: ../../index.php');
                    exit();
                } elseif (\$resultado === 'cambio_clave') {
                    // Redireccionar a la página de cambio de clave
                    header('Location: ../vistas/vista_cambiar_password.php');
                    exit();
                } else {
                    // Redireccionar al login con mensaje de error
                    \$_SESSION['login_error'] = 'Credenciales inválidas';
                    header('Location: ../vistas/vista_login.php');
                    exit();
                }
            }
            break;
            
        case 'logout':
            \$controlador->cerrarSesion();
            header('Location: ../vistas/vista_login.php');
            exit();
            break;
            
        case 'restablecer':
            if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
                \$usuario_o_correo = \$_POST['usuario_o_correo'] ?? '';
                
                \$resultado = \$controlador->restablecerPassword(\$usuario_o_correo);
                
                if (\$resultado) {
                    // Aquí se enviaría el correo con la nueva contraseña
                    \$_SESSION['restablecer_exito'] = 'Se ha enviado una nueva contraseña a su correo electrónico';
                    
                    // En entorno de desarrollo, mostrar la contraseña en la sesión para pruebas
                    \$_SESSION['debug_nueva_clave'] = \$resultado;
                } else {
                    \$_SESSION['restablecer_error'] = 'No se encontró el usuario o correo electrónico';
                }
                
                header('Location: ../vistas/vista_restablecer_password.php');
                exit();
            }
            break;
            
        case 'cambiar_password':
            if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
                \$id_usuario = \$_SESSION['usuario_id'] ?? 0;
                \$password_actual = \$_POST['password_actual'] ?? '';
                \$nueva_password = \$_POST['nueva_password'] ?? '';
                \$confirmar_password = \$_POST['confirmar_password'] ?? '';
                
                \$resultado = \$controlador->cambiarPassword(
                    \$id_usuario, 
                    \$password_actual, 
                    \$nueva_password, 
                    \$confirmar_password
                );
                
                if (\$resultado['exito']) {
                    \$_SESSION['cambio_clave_obligatorio'] = 'N';
                    \$_SESSION['password_exito'] = \$resultado['mensaje'];
                    header('Location: ../../index.php');
                } else {
                    \$_SESSION['password_error'] = \$resultado['mensaje'];
                    header('Location: ../vistas/vista_cambiar_password.php');
                }
                exit();
            }
            break;
            
        case 'registro':
            if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
                \$datos = [
                    'username' => \$_POST['username'] ?? '',
                    'fullname' => \$_POST['fullname'] ?? '',
                    'correo' => \$_POST['correo'] ?? '',
                    'password' => \$_POST['password'] ?? '',
                    'estado' => 'A', // Por defecto activo
                    'cambio_clave_obligatorio' => 'N' // No obligatorio para nuevos registros
                ];
                
                \$resultado = \$controlador->registrarUsuario(\$datos);
                
                if (\$resultado['exito']) {
                    \$_SESSION['registro_exito'] = \$resultado['mensaje'];
                    header('Location: ../vistas/vista_login.php');
                } else {
                    \$_SESSION['registro_error'] = \$resultado['mensaje'];
                    header('Location: ../vistas/vista_registro.php');
                }
                exit();
            }
            break;
    }
}
?> 

    ";
    crearArchivo($archivoControladorLogin, $contenido);
}

// crea vista_cambiar_password.php
function crearVistaCambiarPassword($directorio) {
    $archivoVistaCambiarPassword = "$directorio/accesos/vistas/vista_cambiar_password.php";
    $contenido = "<?php
    session_start();

    // Verificar si el usuario está autenticado
    if (!isset(\$_SESSION['autenticado']) || \$_SESSION['autenticado'] !== true) {
        header('Location: vista_login.php');
        exit();
    }

    // Obtener información del usuario
    \$usuario_id = \$_SESSION['usuario_id'] ?? 0;
    \$usuario_nombre = \$_SESSION['usuario_nombre'] ?? '';
    \$usuario_username = \$_SESSION['usuario_username'] ?? '';
    \$cambio_obligatorio = \$_SESSION['cambio_clave_obligatorio'] ?? false;

    // Obtener mensajes de sesión
    \$password_error = \$_SESSION['password_error'] ?? null;
    \$password_exito = \$_SESSION['password_exito'] ?? null;

    // Limpiar mensajes de sesión una vez utilizados
    unset(\$_SESSION['password_error']);
    unset(\$_SESSION['password_exito']);
    ?>
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Cambiar Contraseña</title>
        <?php include('../headIconos.php'); ?>
        <link rel='stylesheet' href='../css/estilos.css'>
        <style>
            .cambiar-container {
                max-width: 500px;
                margin: 50px auto;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                background-color: #fff;
            }
            .form-group {
                margin-bottom: 15px;
            }
            .alert {
                margin-top: 15px;
                margin-bottom: 15px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='cambiar-container'>
                <h2 class='text-center mb-4'>Cambiar Contraseña</h2>
                
                <?php if (\$cambio_obligatorio): ?>
                    <div class='alert alert-warning'>
                        <strong>Atención:</strong> Es necesario que cambies tu contraseña para continuar.
                    </div>
                <?php endif; ?>
                
                <?php if (\$password_error): ?>
                    <div class='alert alert-danger'><?php echo \$password_error; ?></div>
                <?php endif; ?>
                
                <?php if (\$password_exito): ?>
                    <div class='alert alert-success'><?php echo \$password_exito; ?></div>
                <?php endif; ?>
                
                <p>Usuario: <strong><?php echo htmlspecialchars(\$usuario_username); ?></strong></p>
                
                <form action='../controladores/controlador_login.php?action=cambiar_password' method='POST' id='formCambiar'>
                    <div class='form-group'>
                        <label for='password_actual'>Contraseña Actual:</label>
                        <input type='password' class='form-control' id='password_actual' name='password_actual' required>
                    </div>
                    <div class='form-group'>
                        <label for='nueva_password'>Nueva Contraseña:</label>
                        <input type='password' class='form-control' id='nueva_password' name='nueva_password' required>
                    </div>
                    <div class='form-group'>
                        <label for='confirmar_password'>Confirmar Nueva Contraseña:</label>
                        <input type='password' class='form-control' id='confirmar_password' name='confirmar_password' required>
                    </div>
                    <button type='submit' class='btn btn-primary btn-block'>Cambiar Contraseña</button>
                </form>
                
                <?php if (!\$cambio_obligatorio): ?>
                    <div class='text-center mt-3'>
                        <a href='../../index.php'>Volver al inicio</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
            <script src='https://code.jquery.com/jquery-3.7.1.min.js'></script>
            <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script>
        <script>
            \$(document).ready(function() {
                \$('#formCambiar').on('submit', function(e) {
                    var password1 = \$('#nueva_password').val();
                    var password2 = \$('#confirmar_password').val();
                    
                    if (password1 !== password2) {
                        e.preventDefault();
                        alert('Las contraseñas nuevas no coinciden');
                    }
                });
            });
        </script>
    </body>
    </html> 
    ";
    crearArchivo($archivoVistaCambiarPassword, $contenido);
}    

// crea vista_restablecer_password.php
function crearVistaRestablecerPassword($directorio) {
    $archivoVistaRestablecer = "$directorio/accesos/vistas/vista_restablecer_password.php";
    $contenido = "<?php
    session_start();

    // Obtener mensajes de sesión
    \$restablecer_error = \$_SESSION['restablecer_error'] ?? null;
    \$restablecer_exito = \$_SESSION['restablecer_exito'] ?? null;

    // Limpiar mensajes de sesión una vez utilizados
    unset(\$_SESSION['restablecer_error']);
    unset(\$_SESSION['restablecer_exito']);
    ?>
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Restablecer Contraseña</title>
        <?php include('../headIconos.php'); ?>
        <link rel='stylesheet' href='../css/estilos.css'>
    </head>
    <body>
        <div class='container'>
            <div class='restablecer-container'>
                <h2 class='text-center mb-4'>Restablecer Contraseña</h2>
                
                <?php if (\$restablecer_error): ?>
                    <div class='alert alert-danger'><?php echo \$restablecer_error; ?></div>
                <?php endif; ?>
                
                <?php if (\$restablecer_exito): ?>
                    <div class='alert alert-success'><?php echo \$restablecer_exito; ?></div>
                    <?php if (isset(\$_SESSION['debug_nueva_clave'])): ?>
                        <div class='alert alert-info'>
                            <p><strong>Información de desarrollo:</strong> Se generó la siguiente contraseña:</p>
                            <p>Usuario: <?php echo \$_SESSION['debug_nueva_clave']['username']; ?></p>
                            <p>Nueva clave: <?php echo \$_SESSION['debug_nueva_clave']['nueva_clave']; ?></p>
                        </div>
                        <?php unset(\$_SESSION['debug_nueva_clave']); ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <p class='text-center'>Ingrese su nombre de usuario o correo electrónico para restablecer su contraseña.</p>
                
                <form action='../controladores/controlador_login.php?action=restablecer' method='POST'>
                    <div class='form-group'>
                        <label for='usuario_o_correo'>Usuario o Correo Electrónico:</label>
                        <input type='text' class='form-control' id='usuario_o_correo' name='usuario_o_correo' required>
                    </div>
                    <button type='submit' class='btn btn-primary btn-block'>Restablecer Contraseña</button>
                </form>
                
                <div class='text-center mt-3'>
                    <a href='vista_login.php'>Volver al inicio de sesión</a>
                </div>
            </div>
        </div>
            <script src='https://code.jquery.com/jquery-3.7.1.min.js'></script>
            <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script>    </body>
    </html> 
    ";
    crearArchivo($archivoVistaRestablecer, $contenido);
}

// crea vista_registro.php
function crearVistaRegistro($directorio) {
    $archivoVistaRegistro = "$directorio/accesos/vistas/vista_registro.php";
    $contenido = "<?php
session_start();

// Verificar si el usuario ya está autenticado
if (isset(\$_SESSION['autenticado']) && \$_SESSION['autenticado'] === true) {
    header('Location: ../../index.php');
    exit();
}

// Obtener mensajes de sesión
\$registro_error = \$_SESSION['registro_error'] ?? null;

// Limpiar mensajes de sesión una vez utilizados
unset(\$_SESSION['registro_error']);
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Registro de Usuario</title>
    <?php include('../headIconos.php'); ?>
    <link rel='stylesheet' href='../css/estilos.css'>
</head>
<body>
    <div class='container'>
        <div class='registro-container'>
            <h2 class='text-center mb-4'>Registro de Usuario</h2>
            
            <?php if (\$registro_error): ?>
                <div class='alert alert-danger'><?php echo \$registro_error; ?></div>
            <?php endif; ?>
            
            <form action='../controladores/controlador_login.php?action=registro' method='POST' id='formRegistro'>
                <div class='form-group'>
                    <label for='username'>Nombre de Usuario:</label>
                    <input type='text' class='form-control' id='username' name='username' required>
                </div>
                <div class='form-group'>
                    <label for='fullname'>Nombre Completo:</label>
                    <input type='text' class='form-control' id='fullname' name='fullname' required>
                </div>
                <div class='form-group'>
                    <label for='correo'>Correo Electrónico:</label>
                    <input type='email' class='form-control' id='correo' name='correo' required>
                </div>
                <div class='form-group'>
                    <label for='password'>Contraseña:</label>
                    <input type='password' class='form-control' id='password' name='password' required>
                </div>
                <div class='form-group'>
                    <label for='confirmar_password'>Confirmar Contraseña:</label>
                    <input type='password' class='form-control' id='confirmar_password' name='confirmar_password' required>
                </div>
                <button type='submit' class='btn btn-primary btn-block'>Registrarse</button>
            </form>
            
            <div class='text-center mt-3'>
                <a href='vista_login.php'>¿Ya tienes una cuenta? Inicia sesión aquí</a>
            </div>
        </div>
    </div>
    <script src='https://code.jquery.com/jquery-3.7.1.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script>
    <script>
        \$(document).ready(function() {
            \$('#formRegistro').on('submit', function(e) {
                var password1 = \$('#password').val();
                var password2 = \$('#confirmar_password').val();
                
                if (password1 !== password2) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden');
                    return false;
                }
                
                // Validar correo electrónico
                var correo = \$('#correo').val();
                var correoRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+\$/;
                if (!correoRegex.test(correo)) {
                    e.preventDefault();
                    alert('Por favor, introduce un correo electrónico válido');
                    return false;
                }
                
                // Validar longitud de la contraseña
                if (password1.length < 6) {
                    e.preventDefault();
                    alert('La contraseña debe tener al menos 6 caracteres');
                    return false;
                }
            });
        });
    </script>
</body>
</html> 

";
crearArchivo($archivoVistaRegistro, $contenido);

}


// Función principal
try {
    // Incluir el archivo de conexión
    if (!isset($_SESSION['ruta']) || !isset($_SESSION['nombre_archivo'])) {
        throw new Exception("Configuración de conexión no encontrada");
    }
    $ruta = $_SESSION['ruta'];
    $rutanormalizada = normalizar_ruta($ruta);
    $archivo_conexion = $ruta . '/' . $_SESSION['nombre_archivo'];
    $rutaPrincipalProyecto = rtrim($ruta, '/') . '/index.php';
    $archivoVistaLogin = "$rutanormalizada/vistas/vista_login.php";
    $creaverificasesion = "$rutanormalizada/verificar_sesion.php";

    error_log("CREA PANTALLA LOGIN");
  /*  error_log("ruta: $ruta"); // Imprimir rutaProyecto entra parametro
    error_log("rutanormalizada: $rutanormalizada");
    error_log("INDEXrutaPrincipalProyecto: $rutaPrincipalProyecto");
    error_log("creaverificasesion: $creaverificasesion");
   */
    // Crear el archivo de conexión
    ModificaIndex($rutanormalizada);
    ModificaVerificarSesion($rutanormalizada);
    crearVistaLogin($rutanormalizada);
    crearControladorLogin($rutanormalizada);
    crearVistaRestablecerPassword($rutanormalizada);
    crearVistaCambiarPassword($rutanormalizada);
    crearVistaRegistro($rutanormalizada);


    // Agregar el mensaje de éxito
    $response = [
        'success' => true,
        'message' => 'El Modulo Login se creó exitosamente.',
        'actualizaRutaResponse' => $rutaPrincipalProyecto
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