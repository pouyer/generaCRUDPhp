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
    $registrosPorPagina = isset($_GET['registrosPorPagina']) ? (int)$_GET['registrosPorPagina'] : 10;
    $paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acc_modulo - Tabla</title>
    <?php include('../headIconos.php'); // Incluir los elementos del encabezado iconos?>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="container my-4">
        <h1 class="text-center">Modulos</h1>
        <div class="d-flex justify-content-between mb-3">
            <button type="button" class="btn btn-primary icon-plus" data-bs-toggle="modal" data-bs-target="#modalCrear">
                Crear
            </button>
            <div class="btn-group">
                <button class="btn btn-success dropdown-toggle icon-export" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Exportar
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="../controladores/controlador_acc_modulo.php?action=exportar&formato=excel&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">Excel</a>
                    <a class="dropdown-item" href="../controladores/controlador_acc_modulo.php?action=exportar&formato=csv&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">CSV</a>
                    <a class="dropdown-item" href="../controladores/controlador_acc_modulo.php?action=exportar&formato=txt&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">TXT</a>
                </div>
            </div>
        </div>
        <form method="GET" action="../controladores/controlador_acc_modulo.php" class="d-flex mb-3">
            <div class="input-group" style="width: 100%;">
                <input type="text" name="busqueda" class="form-control" placeholder="Buscar..." value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                <input type="hidden" name="action" value="buscar">
                <input type="hidden" name="registrosPorPagina" value="<?= $registrosPorPagina ?>">
                <input type="hidden" name="pagina" value="<?= $paginaActual ?>">
                    <button type="submit" class="btn btn-secondary icon-search-outline"> </button>
                    <?php if(isset($_GET['busqueda']) && $_GET['busqueda'] !== ''): ?>
                        <a href="../controladores/controlador_acc_modulo.php" class="btn btn-outline-danger icon-eraser"> </a>  <!-- Aqui boton limpiar si requiere nombre -->
                    <?php endif; ?>
                </div>
            </div>
        </form>
        <table class="table table-striped table-sm mt-3">
            <thead>
                <tr>
                    <th>id_modulo</th>
                    <th>nombre_modulo</th>
                    <th>icono</th>
                    <th>orden</th>
                    <th>estado</th>
                    <th>fecha_creacion</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require_once '../modelos/modelo_acc_modulo.php';
                $modelo = new ModeloAcc_modulo();
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
                    <td><?php echo htmlspecialchars($registro['id_modulo']); ?></td>
                    <td><?php echo htmlspecialchars($registro['nombre_modulo']); ?></td>
					<td><i class="<?php echo htmlspecialchars($registro['icono']); ?>"></i></td>
                    <td><?php echo htmlspecialchars($registro['orden']); ?></td>
                    <td><?php echo htmlspecialchars($registro['nombre_estado']); ?></td>
                    <td><?php echo htmlspecialchars($registro['fecha_creacion']); ?></td>
                    <td>
                        <button type="button" class="btn btn-warning icon-edit" data-bs-toggle="modal" data-bs-target="#modalActualizar" data-idActualizar="<?php echo $registro['id_modulo']; ?>"
                           data-id_modulo="<?php echo htmlspecialchars($registro['id_modulo']); ?>"
                           data-nombre_modulo="<?php echo htmlspecialchars($registro['nombre_modulo']); ?>"
                           data-icono="<?php echo htmlspecialchars($registro['icono']); ?>"
                           data-orden="<?php echo htmlspecialchars($registro['orden']); ?>"
                           data-estado="<?php echo htmlspecialchars($registro['estado']); ?>"
                           data-fecha_creacion="<?php echo htmlspecialchars($registro['fecha_creacion']); ?>"> </button>  <!-- Boton Editar si requiere nombre aqui se pone -->
                        <button class="btn btn-danger icon-trash-2" onclick="eliminar('<?php echo htmlspecialchars($registro['id_modulo']); ?>')"> </button>  <!-- Boton Eliminar si requiere nombre aqui se pone -->
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="8">No hay registros disponibles.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="mb-3">
            <form method="GET" class="d-flex">
                <label for="registrosPorPagina" class="mr-2">Registros por página:</label>
                <select id="registrosPorPagina" name="registrosPorPagina" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="10" <?= $registrosPorPagina == 10 ? 'selected' : '' ?>>10</option>
                    <option value="30" <?= $registrosPorPagina == 30 ? 'selected' : '' ?>>30</option>
                    <option value="50" <?= $registrosPorPagina == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $registrosPorPagina == 100 ? 'selected' : '' ?>>100</option>
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
        <div class="modal fade" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCrearLabel">Crear Acc_modulo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formCrear" method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre_modulo">nombre_modulo:</label>
                                     <input type="text" class="form-control" id="nombre_modulo" name="nombre_modulo">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="icono">icono:</label>
                                     <input type="text" class="form-control" id="icono" name="icono">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="orden">orden:</label>
                                     <input type="number" class="form-control" id="orden" name="orden">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="estado" class="form-label">Estado</label>
										<select class="form-select" id="estado" name="estado" required>
											<?php
											$estados = $modelo->obtenerEstados();
											foreach ($estados as $estado):
											?>
											<option value="<?php echo htmlspecialchars($estado['estado']); ?>"><?php echo htmlspecialchars($estado['nombre_estado']); ?></option>
											<?php endforeach; ?>
										</select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary icon-ok-2">Crear</button>
                            <!-- Nuevo botón para abrir una página específica -->
                            <a href="../iconos-web/demo.html" class="btn btn-secondary icon-external-link mt-2" target="_blank">
                                Abrir iconos
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalActualizar" tabindex="-1" aria-labelledby="modalActualizarLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                <div class="modal-body">
                    <form id="formActualizar" method="post">
                     <div class="modal-header">
                         <div class="row">
                             <div class="form-group col-md-8">
                               <h5 class="modal-title">Actualizar Acc_modulo - ID: </h5>
                             </div>
                             <div class="form-group col-md-3">
                                <div class="form-group mb-0 d-flex align-items-center">
                                    <input type="text" class="form-control" id="id_modulo" name="id_modulo" value="<?php echo htmlspecialchars($registro['id_modulo']); ?>" readonly>
                                </div>
                             </div>
                         </div>
                         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                         </button>
                     </div>
                            <div class="row">
                                 <div class="col-md-6 mb-3">
                                     <label for="nombre_modulo">nombre_modulo:</label>
                                     <input type="text" class="form-control" id="nombre_modulo" name="nombre_modulo">
                                </div>
                                 <div class="col-md-6 mb-3">
                                     <label for="icono">icono:</label>
                                     <input type="text" class="form-control" id="icono" name="icono">
                                </div>
                            </div>  <!-- Par no fin de registros  -->
                            <div class="row">
                                 <div class="col-md-6 mb-3">
                                     <label for="orden">orden:</label>
                                     <input type="number" class="form-control" id="orden" name="orden">
                                </div>
                                 <div class="col-md-6 mb-3">
                                     <label for="estado" class="form-label">Estado</label>
										<select class="form-select" id="estado" name="estado" required>
											<?php
											$estados = $modelo->obtenerEstados();
											foreach ($estados as $estado):
											?>
											<option value="<?php echo htmlspecialchars($estado['estado']); ?>"><?php echo htmlspecialchars($estado['nombre_estado']); ?></option>
											<?php endforeach; ?>
										</select>
                                </div>
                            </div> <!--  fin de registros  --> 
                                 <input type="hidden" id="idActualizar" name="idActualizar">
                                 <button type="submit" class="btn btn-warning icon-ok-2">Actualizar</button>
                                    <!-- Nuevo botón para abrir una página específica -->
                                    <a href="../iconos-web/demo.html" class="btn btn-secondary icon-external-link mt-2" target="_blank">
                                        Abrir iconos
                                    </a>
                    </form>
                 </div>
             </div>
         </div>
     </div>
    <!-- Scripts necesarios -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar todos los modales
            var myModalCreate = new bootstrap.Modal(document.getElementById('modalCrear'));
            var myModalUpdate = new bootstrap.Modal(document.getElementById('modalActualizar'));

            // Manejador para el botón crear
            document.querySelector('[data-bs-target="#modalCrear"]').addEventListener('click', function() {
                myModalCreate.show();
            });

            // Manejador del formulario crear
            document.getElementById('formCrear').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('../controladores/controlador_acc_modulo.php?action=crear', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if(data) {
                        myModalCreate.hide();
                        location.reload();
                    } else {
                        alert('Error al crear el registro.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud.');
                });
            });
            console.log('Modal crear:', document.getElementById('modalCrear'));
            console.log('Botón crear:', document.querySelector('[data-bs-target="#modalCrear"]'));
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar el modal de actualización
            var modalActualizar = new bootstrap.Modal(document.getElementById('modalActualizar'));

            // Manejar el evento show.bs.modal
            document.getElementById('modalActualizar').addEventListener('show.bs.modal', function(event) {
                // Botón que activó el modal
                var button = event.relatedTarget;
                var modal = this;

                // Cargar id_modulo
                var valorid_modulo = button.getAttribute('data-id_modulo');
                if(modal.querySelector('#id_modulo')) {
                    modal.querySelector('#id_modulo').value = valorid_modulo;
                }
                // Cargar nombre_modulo
                var valornombre_modulo = button.getAttribute('data-nombre_modulo');
                if(modal.querySelector('#nombre_modulo')) {
                    modal.querySelector('#nombre_modulo').value = valornombre_modulo;
                }
                // Cargar icono
                var valoricono = button.getAttribute('data-icono');
                if(modal.querySelector('#icono')) {
                    modal.querySelector('#icono').value = valoricono;
                }
                // Cargar orden
                var valororden = button.getAttribute('data-orden');
                if(modal.querySelector('#orden')) {
                    modal.querySelector('#orden').value = valororden;
                }
                // Cargar estado
                var valorestado = button.getAttribute('data-estado');
                if(modal.querySelector('#estado')) {
                    modal.querySelector('#estado').value = valorestado;
                }
                // Cargar fecha_creacion
                var valorfecha_creacion = button.getAttribute('data-fecha_creacion');
                if(modal.querySelector('#fecha_creacion')) {
                    modal.querySelector('#fecha_creacion').value = valorfecha_creacion;
                }
                // Cargar fecha_actualiza
                var valorfecha_actualiza = button.getAttribute('data-fecha_actualiza');
                if(modal.querySelector('#fecha_actualiza')) {
                    modal.querySelector('#fecha_actualiza').value = valorfecha_actualiza;
                }
            });

            document.getElementById('formActualizar').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('../controladores/controlador_acc_modulo.php?action=actualizar', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if(data) {
                        modalActualizar.hide();
                        location.reload();
                    } else {
                        alert('Error al actualizar el registro.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud.');
                });
            });

            console.log('Modal actualizar:', document.getElementById('modalActualizar'));
            console.log('Botones actualizar:', document.querySelectorAll('[data-bs-target="#modalActualizar"]'));
        });
    </script>
        <script>
            // Función para eliminar registros con confirmación
            function eliminar(id) {
                if (confirm('¿Estás seguro de que deseas eliminar este registro?')) {
                    // Realizar la petición de eliminación
                    fetch('../controladores/controlador_acc_modulo.php?action=eliminar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + encodeURIComponent(id)
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data) {
                            // Si la eliminación fue exitosa, recargar la página
                            location.reload();
                        } else {
                            // Si hubo un error en la eliminación
                            alert('Error al eliminar el registro.');
                        }
                    })
                    .catch(error => {
                        // Manejo de errores en la petición
                        console.error('Error:', error);
                        alert('Error al eliminar el registro: ' + error.message);
                    });
                }
            }
            // Función para mostrar mensajes de confirmación estilizados
            function mostrarMensaje(mensaje, tipo = 'success') {
                const alertPlaceholder = document.createElement('div');
                alertPlaceholder.innerHTML = `
                    <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                        ${mensaje}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                document.querySelector('.container').insertBefore(alertPlaceholder, document.querySelector('.container').firstChild);
                // Remover el mensaje después de 3 segundos
                setTimeout(() => {
                    alertPlaceholder.remove();
                }, 3000);
            }
        </script>
    <style>
        .modal-backdrop {
            z-index: 1040;
        }
        .modal {
            z-index: 1050;
        }
    </style>
        <script>
            // Inicializar todos los dropdowns
            document.addEventListener('DOMContentLoaded', function() {
                var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
                var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                    return new bootstrap.Dropdown(dropdownToggleEl);
                });
            });
        </script>
    </div>
</body>
</html>
