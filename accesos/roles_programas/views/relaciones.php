<?php
require_once '../controllers/RelacionesController.php'; // Asegúrate de incluir tu archivo de configuración
$controller = new RelacionesController();
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Asignación de Programas a Roles</title>
</head>
<body>
    <div class="container">
        <h1>Asignación de Programas a Roles</h1>
        <div class="row">
            <div class="col-md-4">
                <h3>Roles</h3>
                <ul id="roles-list" class="list-group">
                    <?php foreach ($roles as $rol): ?>
                        <li class='list-group-item' data-id='<?= $rol['id_rol'] ?>' onclick="location.href='?id_rol=<?= $rol['id_rol'] ?>'"><?= $rol['nombre_rol'] ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-md-4">
                <h3>Programas No Asignados</h3>
                <ul id="programas-no-asignados" class="list-group">
                    <?php foreach ($programas_no_asignados as $programa): ?>
                        <li class='list-group-item' data-id='<?= $programa['id_programas'] ?>'><?= $programa['nombre_menu'] ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-md-4">
                <h3>Programas Asignados</h3>
                <ul id="programas-asignados" class="list-group">
                    <?php foreach ($programas_asignados as $programa): ?>
                        <li class='list-group-item' data-id='<?= $programa['id_programas'] ?>'><?= $programa['nombre_menu'] ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-center">
                <button id="mover-a-asignados" class="btn btn-primary"> > </button>
                <button id="mover-a-no-asignados" class="btn btn-primary"> < </button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-center">
                <button id="guardar-cambios" class="btn btn-success">Guardar Cambios</button>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
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

            $('#guardar-cambios').click(function() {
                // Recoger los IDs de los programas asignados
                let programasAsignados = [];
                $('#programas-asignados .list-group-item').each(function() {
                    programasAsignados.push($(this).data('id'));
                });

                console.log(programasAsignados); // Agrega esta línea para depurar

                // Enviar los datos al servidor
                $.ajax({
                    url: '../controllers/guardarCambios.php', // Cambia la ruta según tu estructura
                    type: 'POST',
                    data: {
                        id_rol: <?= $id_rol ?>, // Enviar el ID del rol
                        programas: programasAsignados
                    },
                    success: function(response) {
                        console.log(response); // Agrega esta línea para depurar
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