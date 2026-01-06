<?php
/**
 * GeneraCRUDphp
 *
 * Desarrollado por Carlos Mejia
 * 2024-12-31
 * Version 0.5.0
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
    <title>Programas del Sistema - Administrar</title>
    <?php include('../headIconos.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #00b4db 0%, #0083b0 100%);
            --accent-color: #00d2ff;
        }
        body { background-color: #f0f2f5; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .card { border: none; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .btn-primary { background: var(--primary-gradient); border: none; border-radius: 8px; font-weight: 600; }
        .modal-header { background: var(--primary-gradient); color: white; border-radius: 12px 12px 0 0; }
        .modal-header .btn-close { filter: brightness(0) invert(1); }
        .table thead th { background-color: #f8f9fa; color: #495057; font-weight: 600; font-size: 0.8rem; border-bottom: 2px solid #dee2e6; }
        .status-badge { padding: 4px 10px; border-radius: 50px; font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Administración de Programas</h1>
                <p class="text-muted small">Configura las rutas, iconos y módulos de cada programa</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCrear">
                    <i class="fas fa-plus"></i> Crear Programa
                </button>
                <div class="dropdown">
                    <button class="btn btn-outline-success dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="../controladores/controlador_acc_programa.php?action=exportar&formato=excel&busqueda=<?php echo urlencode($_GET['busqueda'] ?? ''); ?>"><i class="fas fa-file-excel me-2 text-success"></i> Excel</a></li>
                        <li><a class="dropdown-item" href="../controladores/controlador_acc_programa.php?action=exportar&formato=csv&busqueda=<?php echo urlencode($_GET['busqueda'] ?? ''); ?>"><i class="fas fa-file-csv me-2 text-info"></i> CSV</a></li>
                        <li><a class="dropdown-item" href="../controladores/controlador_acc_programa.php?action=exportar&formato=txt&busqueda=<?php echo urlencode($_GET['busqueda'] ?? ''); ?>"><i class="fas fa-file-alt me-2 text-secondary"></i> TXT</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card bg-white p-0 overflow-hidden">
            <div class="bg-light p-3 border-bottom">
                <form method="GET" action="../controladores/controlador_acc_programa.php" class="row g-2">
                    <input type="hidden" name="action" value="buscar">
                    <input type="hidden" name="registrosPorPagina" value="<?= $registrosPorPagina ?>">
                    <div class="col-md-6 col-lg-4">
                        <div class="input-group">
                            <input type="text" name="busqueda" class="form-control" placeholder="Buscar por nombre, ruta..." value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>">
                            <?php if(!empty($_GET['busqueda'])): ?>
                                <a href="../controladores/controlador_acc_programa.php" class="btn btn-outline-danger"><i class="icon-eraser"></i></a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Módulo</th>
                            <th>Nombre Menú</th>
                            <th>Icono</th>
                            <th>Ruta / Archivo</th>
                            <th>Orden</th>
                            <th>Estado</th>
                            <th class="text-center pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require_once '../modelos/modelo_acc_programa.php';
                        $modelo = new ModeloAcc_programa();
                        $termino = $_GET['busqueda'] ?? '';
                        $offset = ($paginaActual - 1) * $registrosPorPagina;

                        if (isset($_GET['action']) && $_GET['action'] === 'buscar') {
                            $totalRegistros = $modelo->contarRegistrosPorBusqueda($termino);
                            $registros = $modelo->buscar($termino, $registrosPorPagina, $offset);
                        } else {
                            $totalRegistros = $modelo->contarRegistros();
                            $registros = $modelo->obtenerTodos($registrosPorPagina, $offset);
                        }

                        if ($registros):
                            foreach ($registros as $reg):
                        ?>
                        <tr>
                            <td class="ps-3 text-muted"><?php echo htmlspecialchars($reg['id_programas']); ?></td>
                            <td><span class="badge bg-secondary-subtle text-secondary border"><?php echo htmlspecialchars($reg['nombre_modulo']); ?></span></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($reg['nombre_menu']); ?></td>
                            <td class="text-center">
                                <i class="<?php echo htmlspecialchars($reg['icono']); ?> h5 mb-0 opacity-75" title="<?php echo htmlspecialchars($reg['icono']); ?>"></i>
                            </td>
                            <td>
                                <div class="small fw-semibold text-primary"><?php echo htmlspecialchars($reg['ruta']); ?></div>
                                <div class="text-muted smaller"><?php echo htmlspecialchars($reg['nombre_archivo']); ?></div>
                            </td>
                            <td class="text-center"><?php echo htmlspecialchars($reg['orden']); ?></td>
                            <td><span class="status-badge <?php echo ($reg['estado'] == 'A' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'); ?>"><?php echo htmlspecialchars($reg['nombre_estado']); ?></span></td>
                            <td class="text-center pe-3">
                                <div class="btn-group shadow-sm">
                                    <button class="btn btn-sm btn-white text-primary border" data-bs-toggle="modal" data-bs-target="#modalActualizar" 
                                                data-id_programas="<?= $reg['id_programas'] ?>"
                                                data-nombre_menu="<?= htmlspecialchars($reg['nombre_menu']) ?>"
                                                data-icono="<?= $reg['icono'] ?>"
                                                data-ruta="<?= htmlspecialchars($reg['ruta']) ?>"
                                                data-nombre_archivo="<?= htmlspecialchars($reg['nombre_archivo']) ?>"
                                                data-orden="<?= $reg['orden'] ?>"
                                                data-estado="<?= $reg['estado'] ?>"
                                                data-id_modulo="<?= $reg['id_modulo'] ?>">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button class="btn btn-sm btn-white text-danger border" onclick="eliminar('<?= $reg['id_programas'] ?>')">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">No hay registros disponibles.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-3 py-3 bg-light border-top d-flex flex-wrap justify-content-between align-items-center">
                <form method="GET" class="d-flex align-items-center gap-2">
                    <span class="small text-muted">Filas:</span>
                    <select name="registrosPorPagina" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                        <?php foreach([10,30,50,100] as $r): ?>
                            <option value="<?= $r ?>" <?= $registrosPorPagina == $r ? 'selected' : '' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="pagina" value="1">
                    <?php if($termino): ?>
                        <input type="hidden" name="busqueda" value="<?= htmlspecialchars($termino) ?>">
                        <input type="hidden" name="action" value="buscar">
                    <?php endif; ?>
                </form>

                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php
                        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
                        for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?>">
                                <a class="page-link" href="?pagina=<?= $i ?>&registrosPorPagina=<?= $registrosPorPagina ?>&busqueda=<?= urlencode($termino) ?><?= $termino ? '&action=buscar' : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Modal Crear -->
    <div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Nuevo Programa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formCrear">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6 text-muted">
                                <label class="form-label small fw-bold">NOMBRE MENÚ</label>
                                <input type="text" class="form-control" name="nombre_menu" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">ICONO (Fontello)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="icono" id="create_icono_prog" placeholder="icon-name">
                                    <button type="button" class="btn btn-dark d-flex align-items-center px-3" onclick="openIconPicker('create_icono_prog')" title="Seleccionar Icono">
                                        <i class="fas fa-icons me-1"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">RUTA DIRECTORIO</label>
                                <input type="text" class="form-control" name="ruta" placeholder="../vistas/">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">NOMBRE ARCHIVO</label>
                                <input type="text" class="form-control" name="nombre_archivo" placeholder="vista_ejemplo.php">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">ORDEN</label>
                                <input type="number" class="form-control" name="orden" value="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">ESTADO</label>
                                <select class="form-select" name="estado" required>
                                    <?php
                                    $estados = $modelo->obtenerEstados();
                                    foreach ($estados as $est): ?>
                                        <option value="<?= $est['estado'] ?>"><?= $est['nombre_estado'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">MÓDULO</label>
                                <select class="form-select" name="id_modulo" required>
                                    <?php
                                    $modulos = $modelo->obtenerModulos();
                                    foreach ($modulos as $mod): ?>
                                        <option value="<?= $mod['id_modulo'] ?>"><?= $mod['nombre_modulo'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 px-4 py-3">
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Guardar Programa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Actualizar -->
    <div class="modal fade" id="modalActualizar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Actualizar Programa #<span id="display_id"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formActualizar">
                    <input type="hidden" name="id_programas" id="update_id">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6 text-muted">
                                <label class="form-label small fw-bold">NOMBRE MENÚ</label>
                                <input type="text" class="form-control" name="nombre_menu" id="update_nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">ICONO</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="icono" id="update_icono_prog">
                                    <button type="button" class="btn btn-dark d-flex align-items-center px-3" onclick="openIconPicker('update_icono_prog')" title="Seleccionar Icono">
                                        <i class="fas fa-icons me-1"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">RUTA DIRECTORIO</label>
                                <input type="text" class="form-control" name="ruta" id="update_ruta">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">NOMBRE ARCHIVO</label>
                                <input type="text" class="form-control" name="nombre_archivo" id="update_archivo">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">ORDEN</label>
                                <input type="number" class="form-control" name="orden" id="update_orden">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">ESTADO</label>
                                <select class="form-select" name="estado" id="update_estado" required>
                                    <?php foreach ($estados as $est): ?>
                                        <option value="<?= $est['estado'] ?>"><?= $est['nombre_estado'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">MÓDULO</label>
                                <select class="form-select" name="id_modulo" id="update_modulo" required>
                                    <?php foreach ($modulos as $mod): ?>
                                        <option value="<?= $mod['id_modulo'] ?>"><?= $mod['nombre_modulo'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 px-4 py-3">
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary px-4">Actualizar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalUpdate = document.getElementById('modalActualizar');
            modalUpdate.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const modal = this;
                
                const id = button.getAttribute('data-id_programas');
                modal.querySelector('#display_id').textContent = id;
                modal.querySelector('#update_id').value = id;
                modal.querySelector('#update_nombre').value = button.getAttribute('data-nombre_menu');
                modal.querySelector('#update_icono_prog').value = button.getAttribute('data-icono');
                modal.querySelector('#update_ruta').value = button.getAttribute('data-ruta');
                modal.querySelector('#update_archivo').value = button.getAttribute('data-nombre_archivo');
                modal.querySelector('#update_orden').value = button.getAttribute('data-orden');
                modal.querySelector('#update_estado').value = button.getAttribute('data-estado');
                modal.querySelector('#update_modulo').value = button.getAttribute('data-id_modulo');
            });

            $('#formCrear').on('submit', function(e) {
                e.preventDefault();
                $.post('../controladores/controlador_acc_programa.php?action=crear', $(this).serialize(), function(data) {
                    if(data) location.reload(); else alert('Error al crear el programa.');
                }, 'json');
            });

            $('#formActualizar').on('submit', function(e) {
                e.preventDefault();
                $.post('../controladores/controlador_acc_programa.php?action=actualizar', $(this).serialize(), function(data) {
                    if(data) location.reload(); else alert('Error al actualizar el programa.');
                }, 'json');
            });
        });

        function eliminar(id) {
            if (confirm('¿Deseas eliminar este programa permanentemente?')) {
                $.post('../controladores/controlador_acc_programa.php?action=eliminar', {id: id}, function(data) {
                    if (data) location.reload(); else alert('Error al eliminar.');
                }, 'json');
            }
        }
    </script>
    <?php include('icon_picker.php'); ?>
</body>
</html>
