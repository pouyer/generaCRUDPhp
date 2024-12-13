<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Administracion</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/estiloMenu.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 menu-fondo">
            <h2 class="text-center">Menu Principal</h2>
            <ul class="list-group">
                <?php
                require_once '../modelos/modelo_menu_principal.php';
                $modelo = new ModeloMenu();
                $modulos = $modelo->obtenerModulos(); // Método que obtiene los módulos

                foreach ($modulos as $index => $modulo): ?>
                    <li class="list-group-item">
                        <strong class="accordion-button" onclick="toggleMenu(this)"><?php echo htmlspecialchars($modulo['modulo']); ?></strong>
                        <ul class="nested-nav">
                            <?php
                            // Obtener los menús para el módulo actual
                            $menus = $modelo->obtenerMenusPorModulo($modulo['modulo']);
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
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