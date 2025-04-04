<?php
require_once '../verificar_sesion.php';
require_once '../../<conexion.php>';
require_once '../controladores/controller_roles_programas.php';

$controller = new ControllerRolesProgramas($conexion); // Pasar la conexión al controlador
$id_rol = isset($_GET['id_rol']) ? $_GET['id_rol'] : null; // Obtener el ID del rol si está presente
$roles = $controller->obtenerRoles(); // Obtener roles
$programas_no_asignados = $controller->obtenerProgramasNoAsignados($id_rol); // Obtener programas no asignados
$programas_asignados = $id_rol ? $controller->obtenerProgramasAsignados($id_rol) : []; // Obtener programas asignados

// Aquí va el HTML para mostrar los roles y programas
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignación de Programas a Roles</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .small-font {
            font-size: 0.85em; /* Ajusta el tamaño de la fuente según sea necesario */
        }
    </style>
</head>
<body  class="small-font"> <!-- Aplicar la clase al body -->>
    <form id="formGuardarCambios" method="POST" action="../controladores/controller_roles_programas.php?action=guardarCambios">
    <input type="hidden" name="id_rol" value="<?= $id_rol ?>">
        <div class="container">
            <h1 class="text-center">Asignación de Programas a Roles</h1>
            <div class="row">
                <div class="col-md-4">
                    <h3 class="text-center">Roles</h3>
                    <ul id="roles-list" class="list-group">
                        <?php foreach ($roles as $rol): ?>
                            <li class='list-group-item' data-id='<?= $rol['id_rol'] ?>' onclick="location.href='?id_rol=<?= $rol['id_rol'] ?>'"><?= $rol['nombre_rol'] ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h3 class="text-center">Programas No Asignados</h3>
                    <ul id="programas-no-asignados" class="list-group">
                        <?php foreach ($programas_no_asignados as $programa): ?>
                            <li class='list-group-item' data-id='<?= $programa['id_programas'] ?>'><?= $programa['nombre_menu'] ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h3 class="text-center">Programas Asignados</h3>
                    <ul id="programas-asignados" class="list-group">
                        <?php foreach ($programas_asignados as $programa): ?>
                            <li class='list-group-item' data-id='<?= $programa['id_programas'] ?>'><?= $programa['nombre_menu'] ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4"> <!-- Columna vacía para mantener el diseño -->
                </div>
                <div class="col-md-4 text-center"> <!-- Centrar el botón en la columna de programas no asignados -->
                        <button id="mover-a-asignados" class="btn btn-primary"> > </button>
                </div>
                    <div class="col-md-4 text-center"> <!-- Centrar el botón en la columna de programas asignados -->
                        <button id="mover-a-no-asignados" class="btn btn-primary"> < </button>
                    </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-center">
              <!--      <button id="guardar-cambios" class="btn btn-success">Guardar Cambios</button> -->
                </div>
            </div>
        </div>
    </form>    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
                // Lógica para seleccionar roles
            $('#roles-list .list-group-item').click(function() {
                $('#roles-list .list-group-item').removeClass('active'); // Eliminar la clase active de todos los roles
                $(this).toggleClass('active'); // Agregar la clase active solo al rol seleccionado
                // Aquí puedes agregar lógica adicional si necesitas hacer algo al seleccionar un rol
            });

            // Lógica para seleccionar programas
            $('.list-group-item').click(function() {
                $(this).toggleClass('active'); // Alternar la clase active al hacer clic
            });

            // Lógica para mover programas entre listas
            $('#mover-a-asignados').click(function() {
                $('#programas-no-asignados .active').each(function() {
                    $(this).removeClass('active').appendTo('#programas-asignados');
                });
            });

            $('#mover-a-no-asignados').click(function() {
                $('#programas-asignados .active').each(function() {
                    $(this).removeClass('active').appendTo('#programas-no-asignados');
                });
            });

            // Manejar el envío del formulario
            $('#formGuardarCambios').on('submit', function(e) {
                e.preventDefault(); // Evitar el envío normal del formulario
                let programasAsignados = [];
                $('#programas-asignados .list-group-item').each(function() {
                    programasAsignados.push($(this).data('id'));
                });

                // Enviar los datos al servidor
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: {
                        id_rol: $('input[name="id_rol"]').val(),
                        programas: programasAsignados
                    },
                    success: function(response) {
                        alert('Cambios guardados exitosamente.');
                    },
                    error: function(xhr, status, error) {
                        alert('Error al guardar los cambios: ' + error);
                    }
                });
            });
        });
    </script>
</body>
</html>
