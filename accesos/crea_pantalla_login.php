<?php
session_start();
ob_start(); // Iniciar buffer de salida para capturar cualquier error/warning
header('Content-Type: application/json');
include "../include/funciones_utilidades.php";

// Modifica el Login para que valide coneccionsi el usuario esta conectado
function ModificaIndex($ruta) {
    $ruta = normalizar_ruta($ruta);
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
    crearArchivo($rutaPrincipalProyecto, $contenido);
}

// Modifica el archivo verificar_sesion.php para que verifique si el usuario ya esta conectado y lo redirija a la pantalla de inicio
function ModificaVerificarSesion($directorio) {
    $creaverificasesion = "$directorio/accesos/verificar_sesion.php";
    $contenido = "<?php
    if (session_status() === PHP_SESSION_NONE) { session_start(); }

    // Determinar la ruta relativa al login de forma dinámica
    \$script_path = str_replace('\\\\', '/', \$_SERVER['SCRIPT_NAME']);
    \$accesos_pos = strpos(\$script_path, '/accesos/');
    
    if (\$accesos_pos !== false) {
        \$depth = substr_count(substr(\$script_path, \$accesos_pos + 9), '/');
        \$prefix = str_repeat('../', \$depth + 1);
    } else {
        \$prefix = './';
        if (strpos(\$script_path, '/vistas/') !== false) \$prefix = '../';
    }

    \$ruta_login = \$prefix . 'accesos/vistas/vista_login.php';
    \$ruta_cambiar_password = \$prefix . 'accesos/vistas/vista_cambiar_password.php';

    if (!isset(\$_SESSION['usuario_id'])) {
        header(\"Location: \$ruta_login\");
        exit;
    }

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
// crea pantalla de vista_login.php
function crearVistaLogin($directorio) {
    $archivoVistaLogin = "$directorio/accesos/vistas/vista_login.php";
    $contenido = "<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Cargar environment si existe
if (file_exists(__DIR__ . '/../../.env')) {
    \$env = parse_ini_file(__DIR__ . '/../../.env');
    if (\$env) {
        foreach (\$env as \$key => \$value) {
            putenv(\"\$key=\$value\");
            \$_ENV[\$key] = \$value;
        }
    }
}

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
    <?php
    \$loginBg = getenv('LOGIN_BG');
    if (\$loginBg) {
        echo \"<style>
            body {
                background: url('../../\$loginBg') no-repeat center center fixed;
                background-size: cover;
            }
            .login-container {
                background: rgba(255, 255, 255, 0.95);
                box-shadow: 0 0 20px rgba(0,0,0,0.2);
            }
        </style>\";
    }
    ?>
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

// crea controlador_login.php
function crearControladorLogin($directorio) {
    $archivoControladorLogin = "$directorio/accesos/controladores/controlador_login.php";
    $contenido = "<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../modelos/modelo_acc_usuario.php';
require_once '../modelos/modelo_acc_log.php';
require_once '../../include/SimpleSMTP.php'; // Incluir la clase SMTP

class ControladorLogin {
    private \$modelo;
    private \$modeloLog;

    public function __construct() {
        \$this->modelo = new ModeloAcc_usuario();
        \$this->modeloLog = new ModeloAcc_log();
    }

    /**
     * Envía un correo electrónico utilizando SimpleSMTP
     */
    private function enviarCorreo(\$destinatario, \$asunto, \$cuerpo) {
        \$host = getenv('SMTP_HOST');
        \$user = getenv('SMTP_USER');
        \$pass = getenv('SMTP_PASS');
        \$port = getenv('SMTP_PORT') ?: 587;
        \$from = getenv('SMTP_FROM') ?: \$user;

        // Si no hay configuración SMTP, intentamos mail() nativo o fallamos controladamente
        if (empty(\$host)) {
            // Intento básico con mail()
            \$headers = \"From: Sistema <no-reply@sistema.com>\\r\\n\";
            \$headers .= \"Content-Type: text/html; charset=UTF-8\\r\\n\";
            return mail(\$destinatario, \$asunto, \$cuerpo, \$headers);
        }

        \$smtp = new SimpleSMTP(\$host, \$user, \$pass, \$port);
        return \$smtp->send(\$destinatario, \$asunto, \$cuerpo, 'Sistema de Usuarios');
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
            \$_SESSION['cambio_clave_obligatorio'] = \$usuario['cambio_clave_obligatorio'] ?? 0;
            // Cargar permisos granulares
            \$permisos = [];
            foreach (\$roles as \$rol) {
                // Consultar permisos por rol
                \$sqlPerm = \"SELECT p.nombre_archivo, pr.permiso_insertar, pr.permiso_actualizar, pr.permiso_eliminar, pr.permiso_exportar 
                            FROM acc_programa_x_rol pr 
                            JOIN acc_programa p ON pr.id_programas = p.id_programas 
                            WHERE pr.id_rol = ?\";
                \$stmtPerm = \$this->modelo->getConexion()->prepare(\$sqlPerm);
                \$stmtPerm->bind_param('i', \$rol['id_rol']);
                \$stmtPerm->execute();
                \$resPerm = \$stmtPerm->get_result();
                while (\$p = \$resPerm->fetch_assoc()) {
                    \$nombre = \$p['nombre_archivo'];
                    if (!isset(\$permisos[\$nombre])) {
                        \$permisos[\$nombre] = ['ins' => 0, 'upd' => 0, 'del' => 0, 'exp' => 0];
                    }
                    // El permiso más alto gana (OR lógico)
                    \$permisos[\$nombre]['ins'] = \$permisos[\$nombre]['ins'] | \$p['permiso_insertar'];
                    \$permisos[\$nombre]['upd'] = \$permisos[\$nombre]['upd'] | \$p['permiso_actualizar'];
                    \$permisos[\$nombre]['del'] = \$permisos[\$nombre]['del'] | \$p['permiso_eliminar'];
                    \$permisos[\$nombre]['exp'] = \$permisos[\$nombre]['exp'] | \$p['permiso_exportar'];
                }
            }
            \$_SESSION['permisos'] = \$permisos;
            
            // Registrar log de acceso
            \$this->modeloLog->registrar(\$usuario['id_usuario'], 'LOGIN', 'acc_usuario', 'Inicio de sesión exitoso');
            
            if (\$_SESSION['cambio_clave_obligatorio'] == 1 || \$_SESSION['cambio_clave_obligatorio'] === '1') {
                return 'cambio_clave';
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
            // Enviar correo con la nueva contraseña
            \$asunto = 'Restablecimiento de Contraseña';
            \$cuerpo = \"
            <html>
            <body>
                <h2>Hola, {\$resultado['username']}</h2>
                <p>Se ha solicitado un restablecimiento de contraseña para tu cuenta.</p>
                <p>Tu nueva contraseña temporal es: <strong>{\$resultado['nueva_clave']}</strong></p>
                <p>Por favor, inicia sesión y cámbiala lo antes posible.</p>
            </body>
            </html>
            \";
            
            \$envio = \$this->enviarCorreo(\$resultado['correo'], \$asunto, \$cuerpo);
            
            if (\$envio) {
                return ['exito' => true, 'mensaje' => 'Se ha enviado una nueva contraseña a su correo.'];
            } else {
                // Fallback si falla el correo
                return ['exito' => true, 'mensaje' => 'Contraseña restablecida, pero no se pudo enviar el correo. Contacte al admin.'];
            }
        }
        
        return false;
    }
    
    public function cambiarPassword(\$id_usuario, \$password_actual, \$nueva_password, \$confirmar_password) {
        // Obtener usuario para verificar la contraseña actual
        \$usuario = \$this->modelo->obtenerPorId(\$id_usuario);
        
        if (!\$usuario || !password_verify(\$password_actual, \$usuario['password'])) {
            return ['exito' => false, 'mensaje' => 'La contraseña actual es incorrecta'];
        }
        
        if (\$nueva_password !== \$confirmar_password) {
            return ['exito' => false, 'mensaje' => 'Las contraseñas nuevas no coinciden'];
        }
        
        // Cambiar la contraseña
        \$resultado = \$this->modelo->cambiarPassword(\$id_usuario, \$nueva_password);
        
        if (\$resultado) {
            // Opcional: Enviar correo de confirmación
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
                // Enviar correo de bienvenida
                if (!empty(\$datos['correo'])) {
                    \$asunto = 'Bienvenido al Sistema';
                    \$cuerpo = \"
                    <html>
                    <body>
                        <h2>¡Bienvenido, {\$datos['fullname']}!</h2>
                        <p>Tu cuenta ha sido creada exitosamente.</p>
                        <p>Usuario: <strong>{\$datos['username']}</strong></p>
                        <p>Contraseña: (La que definiste al registrarte)</p>
                    </body>
                    </html>
                    \";
                    \$this->enviarCorreo(\$datos['correo'], \$asunto, \$cuerpo);
                }

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
                
                if (is_array(\$resultado) && \$resultado['exito']) {
                    \$_SESSION['restablecer_exito'] = \$resultado['mensaje'];
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
                    \$_SESSION['cambio_clave_obligatorio'] = 0;
                    \$_SESSION['password_exito'] = \$resultado['mensaje'];
                    \$source = \$_POST['source'] ?? '';
                    \$url = '../vistas/vista_cambiar_password.php?success=1' . (\$source ? '&source=' . urlencode(\$source) : '');
                    header(\"Location: \$url\");
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
                    'estado' => 'activo', // Por defecto activo
                    'cambio_clave_obligatorio' => 0 // No obligatorio para nuevos registros
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
    if (session_status() === PHP_SESSION_NONE) { session_start(); }

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
                
                <?php if (!\$password_exito): ?>
                <p>Usuario: <strong><?php echo htmlspecialchars(\$usuario_username); ?></strong></p>
                
                <form action='../controladores/controlador_login.php?action=cambiar_password' method='POST' id='formCambiar'>
                    <input type='hidden' name='source' value='<?php echo htmlspecialchars(\$_GET['source'] ?? ''); ?>'>
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
                <?php endif; ?>
                
                <?php 
                // No mostrar volver al inicio si es cambio obligatorio o si viene del menú
                \$es_menu = isset(\$_GET['source']) && \$_GET['source'] === 'menu';
                if (\$cambio_obligatorio != 1 && !\$es_menu): ?>
                    <div class='text-center mt-3'>
                        <a href='../../index.php' class='btn btn-link'>Volver al inicio</a>
                    </div>
                <?php endif; ?>
                
                <?php if (\$password_exito && (\$cambio_obligatorio == 1 || \$cambio_obligatorio === '1')): ?>
                    <div class='text-center mt-3'>
                        <a href='../../index.php' class='btn btn-success'>Continuar al Sistema</a>
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
    if (session_status() === PHP_SESSION_NONE) { session_start(); }

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
if (session_status() === PHP_SESSION_NONE) { session_start(); }

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

    // Copiar librería SMTP
    $origenSMTP = realpath(__DIR__ . '/../templates/SimpleSMTP.php');
    if (!$origenSMTP || !file_exists($origenSMTP)) {
        throw new Exception("No se encuentra la plantilla SimpleSMTP.php en templates/");
    }

    $dirDestinoInclude = $rutanormalizada . '/include';
    if (!is_dir($dirDestinoInclude)) {
        if (!mkdir($dirDestinoInclude, 0777, true)) {
             throw new Exception("No se pudo crear el directorio include en: $dirDestinoInclude");
        }
    }

    $destinoSMTP = $dirDestinoInclude . '/SimpleSMTP.php';
    if (!copy($origenSMTP, $destinoSMTP)) {
        $error = error_get_last();
        throw new Exception("No se pudo copiar SimpleSMTP.php. Detalle: " . $error['message']);
    }


    // Agregar el mensaje de éxito
    ob_clean();
    $response = [
        'success' => true,
        'message' => 'El Modulo Login se creó exitosamente.',
        'actualizaRutaResponse' => $rutaPrincipalProyecto
    ];
    echo json_encode($response);

} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

?>