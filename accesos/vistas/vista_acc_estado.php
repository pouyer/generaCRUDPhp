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
    <title>Acc_estado - Tabla</title>
    <?php include('../headIconos.php'); // Incluir los elementos del encabezado iconos?>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="container">
        <h1 class="text-center">Acc_estado</h1>
        <div class="d-flex justify-content-between mb-3">
            <button type="button" class="btn btn-primary icon-plus" data-bs-toggle="modal" data-bs-target="#modalCrear">
                Crear
            </button>
            <div class="btn-group">
                <button class="btn btn-success dropdown-toggle icon-export" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Exportar
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="../controladores/controlador_acc_estado.php?action=exportar&formato=excel&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">Excel</a>
                    <a class="dropdown-item" href="../controladores/controlador_acc_estado.php?action=exportar&formato=csv&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">CSV</a>
                    <a class="dropdown-item" href="../controladores/controlador_acc_estado.php?action=exportar&formato=txt&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">TXT</a>
                </div>
            </div>
        </div>
        <form method="GET" action="../controladores/controlador_acc_estado.php" class="d-flex mb-3">
            <div class="input-group" style="width: 100%;">
                <input type="text" name="busqueda" class="form-control" placeholder="Buscar..." value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                <input type="hidden" name="action" value="buscar">
                <input type="hidden" name="registrosPorPagina" value="<?= $registrosPorPagina ?>">
                <input type="hidden" name="pagina" value="<?= $paginaActual ?>">
                    <button type="submit" class="btn btn-secondary icon-search-outline"> </button>
                    <?php if(isset($_GET['busqueda']) && $_GET['busqueda'] !== ''): ?>
                        <a href="../controladores/controlador_acc_estado.php" class="btn btn-outline-danger icon-eraser"> </a>  <!-- Aqui boton limpiar si requiere nombre -->
                    <?php endif; ?>
                </div>
            </div>
        </form>
        <table class="table table-striped table-sm mt-3">
            <thead>
                <tr>
                    <th>id_estado</th>
                    <th>tabla</th>
                    <th>estado</th>
                    <th>nombre_estado</th>
                    <th>visible</th>
                    <th>orden</th>
                    <th>fecha_creacion</th>
                    <th>fecha_actualiza</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require_once '../modelos/modelo_acc_estado.php';
                $modelo = new ModeloAcc_estado();
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
                    <td><?php echo htmlspecialchars($registro['id_estado']); ?></td>
                    <td><?php echo htmlspecialchars($registro['tabla']); ?></td>
                    <td><?php echo htmlspecialchars($registro['estado']); ?></td>
                    <td><?php echo htmlspecialchars($registro['nombre_estado']); ?></td>
                    <td><?php echo htmlspecialchars($registro['visible']); ?></td>
                    <td><?php echo htmlspecialchars($registro['orden']); ?></td>
                    <td><?php echo htmlspecialchars($registro['fecha_creacion']); ?></td>
                    <td><?php echo htmlspecialchars($registro['fecha_actualiza']); ?></td>
                    <td>
                        <button type="button" class="btn btn-warning icon-edit" data-bs-toggle="modal" data-bs-target="#modalActualizar" data-idActualizar="<?php echo $registro['id_estado']; ?>"
                           data-id_estado="<?php echo htmlspecialchars($registro['id_estado']); ?>"
                           data-tabla="<?php echo htmlspecialchars($registro['tabla']); ?>"
                           data-estado="<?php echo htmlspecialchars($registro['estado']); ?>"
                           data-nombre_estado="<?php echo htmlspecialchars($registro['nombre_estado']); ?>"
                           data-visible="<?php echo htmlspecialchars($registro['visible']); ?>"
                           data-orden="<?php echo htmlspecialchars($registro['orden']); ?>"
                           data-fecha_creacion="<?php echo htmlspecialchars($registro['fecha_creacion']); ?>"
                           data-fecha_actualiza="<?php echo htmlspecialchars($registro['fecha_actualiza']); ?>"> </button>  <!-- Boton Editar si requiere nombre aqui se pone -->
                        <button class="btn btn-danger icon-trash-2" onclick="eliminar('<?php echo htmlspecialchars($registro['id_estado']); ?>')"> </button>  <!-- Boton Eliminar si requiere nombre aqui se pone -->
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="9">No hay registros disponibles.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="mb-3">
            <form method="GET" class="d-flex">
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
        <div class="modal fade" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCrearLabel">Crear Acc_estado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formCrear" method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tabla">tabla:</label>
                                     <input type="text" class="form-control" id="tabla" name="tabla">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="estado">estado:</label>
                                     <input type="text" class="form-control" id="estado" name="estado">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre_estado">nombre_estado:</label>
                                     <input type="text" class="form-control" id="nombre_estado" name="nombre_estado">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="visible">visible:</label>
                                     <input type="number" class="form-control" id="visible" name="visible">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="orden">orden:</label>
                                     <input type="number" class="form-control" id="orden" name="orden">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary icon-ok-2">Crear</button>
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
                               <h5 class="modal-title">Actualizar Acc_estado - ID: </h5>
                             </div>
                             <div class="form-group col-md-3">
                                <div class="form-group mb-0 d-flex align-items-center">
                                    <input type="text" class="form-control" id="id_estado" name="id_estado" value="<?php echo htmlspecialchars($registro['id_estado']); ?>" readonly>
                                </div>
                             </div>
                         </div>
                         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                         </button>
                     </div>
                            <div class="row">
                                 <div class="col-md-6 mb-3">
                                     <label for="tabla">tabla:</label>
                                     <input type="text" class="form-control" id="tabla" name="tabla">
                                </div>
                                 <div class="col-md-6 mb-3">
                                     <label for="estado">estado:</label>
                                     <input type="text" class="form-control" id="estado" name="estado">
                                </div>
                            </div>  <!-- Par no fin de registros  -->
                            <div class="row">
                                 <div class="col-md-6 mb-3">
                                     <label for="nombre_estado">nombre_estado:</label>
                                     <input type="text" class="form-control" id="nombre_estado" name="nombre_estado">
                                </div>
                                 <div class="col-md-6 mb-3">
                                     <label for="visible">visible:</label>
                                     <input type="number" class="form-control" id="visible" name="visible">
                                </div>
                            </div>  <!-- Par no fin de registros  -->
                            <div class="row">
                                 <div class="col-md-6 mb-3">
                                     <label for="orden">orden:</label>
                                     <input type="number" class="form-control" id="orden" name="orden">
                                </div>
                            </div> <!--  fin de registros  --> 
                                 <input type="hidden" id="idActualizar" name="idActualizar">
                                 <button type="submit" class="btn btn-warning icon-ok-2">Actualizar</button>
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
                fetch('../controladores/controlador_acc_estado.php?action=crear', {
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

                // Cargar id_estado
                var valorid_estado = button.getAttribute('data-id_estado');
                if(modal.querySelector('#id_estado')) {
                    modal.querySelector('#id_estado').value = valorid_estado;
                }
                // Cargar tabla
                var valortabla = button.getAttribute('data-tabla');
                if(modal.querySelector('#tabla')) {
                    modal.querySelector('#tabla').value = valortabla;
                }
                // Cargar estado
                var valorestado = button.getAttribute('data-estado');
                if(modal.querySelector('#estado')) {
                    modal.querySelector('#estado').value = valorestado;
                }
                // Cargar nombre_estado
                var valornombre_estado = button.getAttribute('data-nombre_estado');
                if(modal.querySelector('#nombre_estado')) {
                    modal.querySelector('#nombre_estado').value = valornombre_estado;
                }
                // Cargar visible
                var valorvisible = button.getAttribute('data-visible');
                if(modal.querySelector('#visible')) {
                    modal.querySelector('#visible').value = valorvisible;
                }
                // Cargar orden
                var valororden = button.getAttribute('data-orden');
                if(modal.querySelector('#orden')) {
                    modal.querySelector('#orden').value = valororden;
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
                fetch('../controladores/controlador_acc_estado.php?action=actualizar', {
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
                    fetch('../controladores/controlador_acc_estado.php?action=eliminar', {
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
