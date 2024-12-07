<?php
    $registrosPorPagina = isset($_GET['registrosPorPagina']) ? (int)$_GET['registrosPorPagina'] : 10;
    $paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programa - Tabla</title>
    <?php include('../headIconos.php'); // Incluir los elementos del encabezado iconos?>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="container">
        <h1 class="text-center">Programas</h1>
        <div class="d-flex justify-content-between mb-3">
            <button class="btn btn-primary icon-plus" data-toggle="modal" data-target="#modalCrear">Crear</button>
            <div class="btn-group">
                <button class="btn btn-success dropdown-toggle icon-export" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Exportar
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="../controladores/controlador_acc_programa.php?action=exportar&formato=excel&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">Excel</a>
                    <a class="dropdown-item" href="../controladores/controlador_acc_programa.php?action=exportar&formato=csv&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">CSV</a>
                    <a class="dropdown-item" href="../controladores/controlador_acc_programa.php?action=exportar&formato=txt&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">TXT</a>
                </div>
            </div>
        </div>
        <form method="GET" action="../controladores/controlador_acc_programa.php" class="form-inline mb-3">
            <div class="input-group" style="width: 100%;">
                <input type="text" name="busqueda" class="form-control" placeholder="Buscar..." value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                <input type="hidden" name="action" value="buscar">
                <input type="hidden" name="registrosPorPagina" value="<?= $registrosPorPagina ?>">
                <input type="hidden" name="pagina" value="<?= $paginaActual ?>">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-secondary icon-search-outline"> </button> <!-- Aqui boton Buscar si requiere nombre -->
                    <?php if(isset($_GET['busqueda']) && $_GET['busqueda'] !== ''): ?>
                        <a href="../controladores/controlador_acc_programa.php" class="btn btn-outline-danger icon-eraser"></a> <!-- Aqui boton Limpiar si requiere nombre -->
                    <?php endif; ?>
                </div>
            </div>
        </form>
        <table class="table table-striped table-sm mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre Menu</th>
                    <th>Ruta</th>
                    <th>Nombre Archivo</th>
                    <th>Orden</th>
                    <th>Estado</th>
                    <th>Modulo</th>
                    <th>fecha_creacion</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require_once '../modelos/modelo_acc_programa.php';
                $modelo = new ModeloAcc_programa();
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
                    <td><?php echo htmlspecialchars($registro['id_programas']); ?></td>
                    <td><?php echo htmlspecialchars($registro['nombre_menu']); ?></td>
                    <td><?php echo htmlspecialchars($registro['ruta']); ?></td>
                    <td><?php echo htmlspecialchars($registro['nombre_archivo']); ?></td>
                    <td><?php echo htmlspecialchars($registro['orden']); ?></td>
                    <td><?php echo htmlspecialchars($registro['nombre_estado']); ?></td>
                    <td><?php echo htmlspecialchars($registro['nombre_modulo']); ?></td>
                    <td><?php echo htmlspecialchars($registro['fecha_creacion']); ?></td>
                    <td>
                        <button class="btn btn-warning icon-edit" data-toggle="modal" data-target="#modalActualizar" data-idActualizar="<?php echo $registro['id_programas']; ?>"
                           data-id_programas="<?php echo htmlspecialchars($registro['id_programas']); ?>"
                           data-nombre_menu="<?php echo htmlspecialchars($registro['nombre_menu']); ?>"
                           data-ruta="<?php echo htmlspecialchars($registro['ruta']); ?>"
                           data-nombre_archivo="<?php echo htmlspecialchars($registro['nombre_archivo']); ?>"
                           data-orden="<?php echo htmlspecialchars($registro['orden']); ?>"
                           data-estado="<?php echo htmlspecialchars($registro['estado']); ?>"
                           data-id_modulo="<?php echo htmlspecialchars($registro['id_modulo']); ?>"
                           data-fecha_creacion="<?php echo htmlspecialchars($registro['fecha_creacion']); ?>"> </button> <!-- Boton Editar si requiere nombre aqui se pone -->
                        <button class="btn btn-danger icon-trash-2" onclick="eliminar('<?php echo htmlspecialchars($registro['id_programas']); ?>')"> </button> <!-- Boton Eliminar si requiere nombre aqui se pone -->
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="9">No hay registros disponibles.</td></tr>
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
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Programa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formCrear">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="nombre_menu">Nombre Menu:</label>
                                     <input type="text" class="form-control" id="nombre_menu" name="nombre_menu">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="ruta">Ruta:</label>
                                     <input type="text" class="form-control" id="ruta" name="ruta">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="nombre_archivo">Nombre Programa:</label>
                                     <input type="text" class="form-control" id="nombre_archivo" name="nombre_archivo">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="orden">Orden:</label>
                                     <input type="number" class="form-control" id="orden" name="orden">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="estado">Estado:</label>
                                    <select class="form-control" id="estado" name="estado" required>
                                        <option value="A">Activo</option>
                                        <option value="I">Inactivo</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="id_modulo">Modulo:</label>
                                    <select class="form-control" id="id_modulo" name="id_modulo" required>
                                        <?php
                                        $modulos = $modelo->obtenerModulos(); // Obtener los módulos
                                        foreach ($modulos as $modulo):
                                        ?>
                                            <option value="<?php echo htmlspecialchars($modulo['id_modulo']); ?>"><?php echo htmlspecialchars($modulo['nombre_modulo']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Crear</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalActualizar" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                <div class="modal-body">
                    <form id="formActualizar">
                        <div class="modal-header">
                            <div class="form-row">
                                <div class="form-group col-md-8">
                                <h5 class="modal-title">Editar Programa - ID: </h5>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="form-group mb-0 d-flex align-items-center">
                                        <input type="text" class="form-control" id="id_programas" name="id_programas" value="<?php echo htmlspecialchars($registro['id_programas']); ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                    <label for="nombre_menu">Nombre Menu:</label>
                                    <input type="text" class="form-control" id="nombre_menu" name="nombre_menu">
                            </div>
                            <div class="form-group col-md-6">
                                    <label for="ruta">Ruta:</label>
                                    <input type="text" class="form-control" id="ruta" name="ruta">
                            </div>
                        </div>  <!-- Par no fin de registros  -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                    <label for="nombre_archivo">Nombre Programa:</label>
                                    <input type="text" class="form-control" id="nombre_archivo" name="nombre_archivo">
                            </div>
                            <div class="form-group col-md-6">
                                    <label for="orden">orden:</label>
                                    <input type="number" class="form-control" id="orden" name="orden">
                            </div>
                        </div>  <!-- Par no fin de registros  -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="estado">Estado:</label>
                                <select class="form-control" id="estado" name="estado" required>
                                    <option value="A">Activo</option>
                                    <option value="I">Inactivo</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="id_modulo">Modulo:</label>
                                <select class="form-control" id="id_modulo" name="id_modulo" required>
                                    <?php
                                    $modulos = $modelo->obtenerModulos(); // Obtener los módulos
                                    foreach ($modulos as $modulo):
                                    ?>
                                        <option value="<?php echo htmlspecialchars($modulo['id_modulo']); ?>"><?php echo htmlspecialchars($modulo['nombre_modulo']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <!--  fin de registros  -->
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
                        url: '../controladores/controlador_acc_programa.php?action=eliminar', // Cambia esto a la ruta correcta
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
                        url: '../controladores/controlador_acc_programa.php?action=crear', // Cambia esto a la ruta correcta
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
                        url: '../controladores/controlador_acc_programa.php?action=actualizar', // Cambia esto a la ruta correcta
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
                 modal.find('#id_programas').val(button.data('id_programas'));
                 modal.find('#nombre_menu').val(button.data('nombre_menu'));
                 modal.find('#ruta').val(button.data('ruta'));
                 modal.find('#nombre_archivo').val(button.data('nombre_archivo'));
                 modal.find('#orden').val(button.data('orden'));
                 modal.find('#estado').val(button.data('estado'));
                 modal.find('#id_modulo').val(button.data('id_modulo'));
                 modal.find('#fecha_creacion').val(button.data('fecha_creacion'));
                });
            });
        </script>
    </div>
</body>
</html>
