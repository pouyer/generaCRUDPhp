<?php
    $registrosPorPagina = isset($_GET['registrosPorPagina']) ? (int)$_GET['registrosPorPagina'] : 10;
    $paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acc_rol_x_usuario - Tabla</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="container">
        <h1 class="text-center">Acc_rol_x_usuario</h1>
        <div class="d-flex justify-content-between mb-3">
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalCrear">Crear</button>
            <div class="btn-group">
                <button class="btn btn-success dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Exportar
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="../controladores/controlador_acc_rol_x_usuario.php?action=exportar&formato=excel&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">Excel</a>
                    <a class="dropdown-item" href="../controladores/controlador_acc_rol_x_usuario.php?action=exportar&formato=csv&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">CSV</a>
                    <a class="dropdown-item" href="../controladores/controlador_acc_rol_x_usuario.php?action=exportar&formato=txt&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">TXT</a>
                </div>
            </div>
        </div>
        <form method="GET" action="../controladores/controlador_acc_rol_x_usuario.php" class="form-inline mb-3">
            <div class="input-group" style="width: 100%;">
                <input type="text" name="busqueda" class="form-control" placeholder="Buscar..." value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                <input type="hidden" name="action" value="buscar">
                <input type="hidden" name="registrosPorPagina" value="<?= $registrosPorPagina ?>">
                <input type="hidden" name="pagina" value="<?= $paginaActual ?>">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-secondary">Buscar</button>
                    <?php if(isset($_GET['busqueda']) && $_GET['busqueda'] !== ''): ?>
                        <a href="../controladores/controlador_acc_rol_x_usuario.php" class="btn btn-outline-danger">Limpiar</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        <table class="table table-striped table-sm mt-3">
            <thead>
                <tr>
                    <th>id_usuario</th>
                    <th>id_rol</th>
                    <th>fecha_creacion</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require_once '../modelos/modelo_acc_rol_x_usuario.php';
                $modelo = new ModeloAcc_rol_x_usuario();
                $termino = $_GET['busqueda'] ?? ''; // Inicializar la variable $termino
                $registrosPorPagina = isset($_GET['registrosPorPagina']) ? (int)$_GET['registrosPorPagina'] : 10;
                $paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
 			   $offset = ($paginaActual - 1) * $registrosPorPagina; // Calcular el offset para la paginación
				   	// Verifica si se está realizando una búsqueda 
 			   	if (isset($_GET['action']) && $_GET['action'] === 'buscar') { 
 			   	// Si se está buscando, obtenemos los registros filtrados 
 			   	$termino = $_GET['busqueda'] ?? ''; 
 			   	$totalRegistros = $modelo->contarRegistrosPorBusqueda($termino); // Contar registros que coinciden con la búsqueda
 			   	$registros = $modelo->buscar($termino, $registrosPorPagina, $offset); // Llama a la función de búsqueda con paginación
 			   } else { 
 			   // Si no se está buscando, obtenemos todos los registros con paginación 
 			    $totalRegistros = $modelo->contarRegistros(); // Total de registros en la base de datos
 			   	$registros = $modelo->obtenerTodos($registrosPorPagina, $offset); // Llama a la función para obtener todos
 			   }
 			   // Verifica si hay registros y los muestra
                if ($registros):
                    foreach ($registros as $registro):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($registro['id_usuario']); ?></td>
                    <td><?php echo htmlspecialchars($registro['id_rol']); ?></td>
                    <td><?php echo htmlspecialchars($registro['fecha_creacion']); ?></td>
                    <td>
                        <button class="btn btn-warning" data-toggle="modal" data-target="#modalActualizar" data-idActualizar="<?php echo $registro['id_usuario']; ?>"
                           data-id_usuario="<?php echo htmlspecialchars($registro['id_usuario']); ?>"
                           data-id_rol="<?php echo htmlspecialchars($registro['id_rol']); ?>"
                           data-fecha_creacion="<?php echo htmlspecialchars($registro['fecha_creacion']); ?>">Actualizar</button>
                        <button class="btn btn-danger" onclick="eliminar('<?php echo htmlspecialchars($registro['id_usuario']); ?>')">Eliminar</button>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="4">No hay registros disponibles.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="mb-3">
            <form method="GET" class="form-inline">
                <label for="registrosPorPagina" class="mr-2">Registros por página:</label>
                <select id="registrosPorPagina" name="registrosPorPagina" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="10" <?= $registrosPorPagina == 10 ? 'selected' : '' ?>>10</option>
                    <option value="20" <?= $registrosPorPagina == 20 ? 'selected' : '' ?>>20</option>
                    <option value="30" <?= $registrosPorPagina == 30 ? 'selected' : '' ?>>30</option>
                    <option value="50" <?= $registrosPorPagina == 50 ? 'selected' : '' ?>>50</option>
                </select>
                <input type="hidden" name="pagina" value="<?= $paginaActual ?>">
            </form>
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php
                // Verifica si se está realizando una búsqueda
                if (isset($_GET['action']) && $_GET['action'] === 'buscar') {
                $termino = $_GET['busqueda'] ?? ''; // Inicializar la variable $termino
                    $totalRegistros = $modelo->contarRegistrosPorBusqueda($termino); // Contar registros que coinciden con la búsqueda
                } else {
                    $totalRegistros = $modelo->contarRegistros(); // Total de registros en la base de datos
                }
                $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
                for ($i = 1; $i <= $totalPaginas; $i++):
                ?>
                    <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?> ">
                        <a class="page-link" href="?pagina=<?= $i ?>&registrosPorPagina=<?= $registrosPorPagina ?>&busqueda=<?= urlencode($termino) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <div class="modal fade" id="modalCrear" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Acc_rol_x_usuario</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formCrear">
                            <button type="submit" class="btn btn-primary">Crear</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalActualizar" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Actualizar Acc_rol_x_usuario</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formActualizar">
                            <div class="form-group">
                                <label for="id_usuario">id_usuario:</label>
                                <input type="text" class="form-control" id="id_usuario" name="id_usuario" value="<?php echo htmlspecialchars($registro['id_usuario']); ?>" readonly>
                            <div class="form-group">
                                <label for="id_rol">id_rol:</label>
                                <input type="text" class="form-control" id="id_rol" name="id_rol" value="<?php echo htmlspecialchars($registro['id_rol']); ?>" readonly>
                            <input type="hidden" id="idActualizar" name="idActualizar">
                            <button type="submit" class="btn btn-warning">Actualizar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function eliminar(id) {
                if (confirm('¿Estás seguro de que deseas eliminar este registro?')) {
                    $.ajax({
                        type: 'POST',
                        url: '../controladores/controlador_acc_rol_x_usuario.php?action=eliminar', // Cambia esto a la ruta correcta
                        data: { id: id },
                        success: function(response) {
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                            alert('Error al eliminar el registro.');
                        }
                    });
                }
            }
        </script>
        <script>
            $(document).ready(function() {
                $('#formCrear').on('submit', function(e) {
                    e.preventDefault(); // Evitar el envío normal del formulario
                    $.ajax({
                        type: 'POST',
                        url: '../controladores/controlador_acc_rol_x_usuario.php?action=crear', // Cambia esto a la ruta correcta
                        data: $(this).serialize(),
                        success: function(response) {
                            location.reload(); // Recargar la página para ver los cambios
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                            alert('Error al crear el registro.');
                        }
                    });
                });
                $('#formActualizar').on('submit', function(e) {
                    e.preventDefault(); // Evitar el envío normal del formulario
                    console.log($(this).serialize()); // Verificar los datos enviados
                    $.ajax({
                        type: 'POST',
                        url: '../controladores/controlador_acc_rol_x_usuario.php?action=actualizar', // Cambia esto a la ruta correcta
                        data: $(this).serialize(),
                        success: function(response) {
                            location.reload(); // Recargar la página para ver los cambios
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                            alert('Error al actualizar el registro.');
                        }
                    });
                });
                $('#modalActualizar').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget);
                    var id = button.data('idActualizar');
                    var modal = $(this);
                    modal.find('#idActualizar').val(id);
                modal.find('#id_usuario').val(button.data('id_usuario'));
                modal.find('#id_rol').val(button.data('id_rol'));
                modal.find('#fecha_creacion').val(button.data('fecha_creacion'));
                });
            });
        </script>
    </div>
</body>
</html>
