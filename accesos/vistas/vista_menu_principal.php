<?php
/**
 * GeneraCRUDphp
 *
 * es desarrollada para ajilizar el desarrollo de aplicaciones PHP
 * permitir la administracion de tablas creando leer, actualizar, editar y elimar reguistros
 * Desarrollado por Carlos Mejia
 * 2024-12-06
 * Version 0.4.0
 *  
 */
require_once '../verificar_sesion.php';
require_once '../../config/config.php'; // Incluir archivo de configuración
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Administracion</title>
   <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"> -->
    <link rel="stylesheet" href="../css/estiloMenu.css">
    <?php include('../headIconos.php'); // Incluir los elementos del encabezado iconos?>
</head>
<body>

<div class="header-container">
        <div class="container">
            <div class="user-info">
                <h2 class="welcome-text">Bienvenido, <?php echo htmlspecialchars($usuario_nombre); ?></h2> 
                <a href="../controladores/controlador_login.php?action=logout" class="btn btn-danger logout-btn">
                    <i class="icon-logout"></i> Cerrar Sesión
                </a>
            </div>
        </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 menu-fondo">
            <h2 class="text-center">Menu Principal</h2>
            <ul class="list-group">
                <?php
                require_once '../modelos/modelo_menu_principal.php';
                $modelo = new ModeloMenu();
                $modulos = $modelo->obtenerModulos($usuario_id); // Método que obtiene los módulos

                foreach ($modulos as $index => $modulo): ?>
                    <li class="list-group-item">
                      <!--  <strong class="accordion-button" onclick="toggleMenu(this)"><?php echo htmlspecialchars($modulo['modulo']); ?></strong>  -->
                        <!-- Cambié el icono a un <i> para que se vea mejor -->
                        <strong class="accordion-button" onclick="toggleMenu(this)">
                            <i class="<?php echo htmlspecialchars($modulo['icono_modulo']); ?>"> . </i> <!-- Mostrar el icono -->
                            <?php echo htmlspecialchars($modulo['modulo']); ?>
                        </strong>
                        
                        <ul class="nested-nav">
                            <?php
                            // Obtener los menús para el módulo actual
                            $menus = $modelo->obtenerMenusPorModulo($modulo['modulo'], $usuario_id);
                            if (empty($menus)): ?>
                                <li>No hay menús disponibles para este módulo.</li>
                            <?php else:
                                foreach ($menus as $menu):
                                    $ruta = rtrim($menu['ruta_programa'], '/');
                                    $nombrePrograma = isset($menu['nombre_programaPHP']) ? $menu['nombre_programaPHP'] : 'programa_no_definido'; // Manejo de error
                                    $url = $ruta . '/' . $nombrePrograma;
                                    ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($url); ?>" target="iframeTrabajo">
                                            <i class="<?php echo htmlspecialchars($menu['icono_programa']); ?>"> </i>
                                            <?php echo htmlspecialchars($menu['nombre_menu']); ?>
                                        </a>
                                    </li>
                                <?php endforeach;
                            endif; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-10">
            <iframe name="iframeTrabajo" style="width: 105%; height: 600px;"></iframe>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <span><?php echo htmlspecialchars(getVersionInfo()); ?></span>
    </div>
</footer>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script>
    function toggleMenu(button) {
        const parentLi = button.parentElement;
        parentLi.classList.toggle('active');
        const nestedNav = parentLi.querySelector('.nested-nav');
        if (nestedNav) {
            nestedNav.style.display = nestedNav.style.display === 'block' ? 'none' : 'block';
        }
    }
</script>
</body>
</html>