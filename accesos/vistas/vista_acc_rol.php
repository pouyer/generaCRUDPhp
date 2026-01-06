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
    <title>Roles de Acceso - Administrar</title>
    <?php include('../headIconos.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            --accent-color: #00d2ff;
        }
        body { background-color: #f8f9fa; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: transform 0.3s ease; }
        .table-container { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .btn-primary { background: var(--primary-gradient); border: none; padding: 10px 25px; border-radius: 10px; font-weight: 600; box-shadow: 0 4px 15px rgba(37, 117, 252, 0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37, 117, 252, 0.4); }
        .modal-content { border: none; border-radius: 20px; overflow: hidden; }
        .modal-header { background: var(--primary-gradient); color: white; border: none; padding: 20px 30px; }
        .modal-header .btn-close { filter: brightness(0) invert(1); }
        .modal-footer { border-top: 1px solid #eee; padding: 20px 30px; }
        .table thead th { background-color: #fcfcfc; border-bottom: 2px solid #f0f0f0; color: #666; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; }
        .table tbody td { vertical-align: middle; color: #444; border-bottom: 1px solid #f8f9fa; padding: 12px 15px; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
        .pagination .page-link { border: none; margin: 0 3px; border-radius: 8px; color: #666; font-weight: 500; }
        .pagination .page-item.active .page-link { background: var(--primary-gradient); color: white; box-shadow: 0 4px 10px rgba(37, 117, 252, 0.3); }
        .search-box { border-radius: 10px; border: 1px solid #e0e0e0; padding: 10px 20px; transition: all 0.3s ease; }
        .search-box:focus { box-shadow: 0 0 0 4px rgba(37, 117, 252, 0.1); border-color: #2575fc; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
                    <div class="p-4 bg-primary text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                        <div>
                            <h2 class="h4 mb-0 fw-bold">Gestión de Roles</h2>
                            <p class="mb-0 opacity-75 small">Administra los roles y permisos del sistema</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-white text-primary fw-bold d-flex align-items-center gap-2 px-3 py-2 bg-white rounded-3 shadow-sm border-0" data-bs-toggle="modal" data-bs-target="#modalCrear">
                                <i class="fas fa-plus"></i> Nuevo Rol
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-white text-muted fw-bold d-flex align-items-center gap-2 px-3 py-2 bg-white rounded-3 shadow-sm border-0" data-bs-toggle="dropdown">
                                    <i class="fas fa-file-export"></i> Exportar
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li><a class="dropdown-item py-2" href="../controladores/controlador_acc_rol.php?action=exportar&formato=excel"><i class="icon-file-excel text-success me-2"></i> Excel</a></li>
                                    <li><a class="dropdown-item py-2" href="../controladores/controlador_acc_rol.php?action=exportar&formato=csv"><i class="icon-doc-text text-info me-2"></i> CSV</a></li>
                                    <li><a class="dropdown-item py-2" href="../controladores/controlador_acc_rol.php?action=exportar&formato=txt"><i class="icon-doc-inv text-secondary me-2"></i> TXT</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
            <div class="card-body p-0">
                <div class="p-4 border-bottom bg-light">
                    <form method="GET" action="../controladores/controlador_acc_rol.php" class="row g-3">
                        <input type="hidden" name="action" value="buscar">
                        <input type="hidden" name="registrosPorPagina" value="<?= $registrosPorPagina ?>">
                        <div class="col-md-8 col-lg-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="icon-search-outline text-muted"></i></span>
                                <input type="text" name="busqueda" class="form-control border-start-0 search-box shadow-none" placeholder="Buscar por nombre de rol..." value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>">
                                <?php if(isset($_GET['busqueda']) && $_GET['busqueda'] !== ''): ?>
                                    <a href="../controladores/controlador_acc_rol.php" class="btn btn-outline-secondary d-flex align-items-center"><i class="icon-eraser"></i></a>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-secondary">Buscar</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Nombre del Rol</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th class="text-center pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            require_once '../modelos/modelo_acc_rol.php';
                            $modelo = new ModeloAcc_rol();
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
                                foreach ($registros as $registro):
                                    $statusClass = ($registro['estado'] == 'A') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                            ?>
                            <tr>
                                <td class="ps-4 text-muted fw-medium"><?php echo htmlspecialchars($registro['id_rol']); ?></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($registro['nombre_rol']); ?></td>
                                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($registro['nombre_estado']); ?></span></td>
                                <td class="text-muted"><?php echo htmlspecialchars($registro['fecha_creacion']); ?></td>
                                <td class="text-center pe-4">
                                    <div class="btn-group shadow-sm">
                                        <button type="button" class="btn btn-sm btn-white text-warning border" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalActualizar"
                                                data-id_rol="<?php echo htmlspecialchars($registro['id_rol']); ?>"
                                                data-nombre_rol="<?php echo htmlspecialchars($registro['nombre_rol']); ?>"
                                                data-estado="<?php echo htmlspecialchars($registro['estado']); ?>"
                                                title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-white text-danger border" 
                                                onclick="eliminar('<?php echo htmlspecialchars($registro['id_rol']); ?>')"
                                                title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted">No se encontraron roles disponibles.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-light border-top d-flex justify-content-between align-items-center">
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Mostrar:</span>
                        <select name="registrosPorPagina" class="form-select form-select-sm shadow-none" onchange="this.form.submit()" style="width: auto;">
                            <?php foreach([10, 30, 50, 100] as $r): ?>
                                <option value="<?= $r ?>" <?= $registrosPorPagina == $r ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="pagina" value="1">
                        <?php if($termino): ?>
                            <input type="hidden" name="busqueda" value="<?= htmlspecialchars($termino) ?>">
                            <input type="hidden" name="action" value="buscar">
                        <?php endif; ?>
                    </form>

                    <nav aria-label="Navegación de páginas">
                        <ul class="pagination pagination-sm mb-0">
                            <?php
                            $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
                            for ($i = 1; $i <= $totalPaginas; $i++):
                            ?>
                                <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?>">
                                    <a class="page-link shadow-none" href="?pagina=<?= $i ?>&registrosPorPagina=<?= $registrosPorPagina ?>&busqueda=<?= urlencode($termino) ?><?= $termino ? '&action=buscar' : '' ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear -->
    <div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Nuevo Rol de Acceso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formCrear">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Nombre del Rol</label>
                            <input type="text" class="form-control p-3 border-light-subtle bg-light shadow-none" name="nombre_rol" required placeholder="Ej: Administrador, Auditor...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Estado Inicial</label>
                            <select class="form-select p-3 border-light-subtle bg-light shadow-none" name="estado" required>
                                <?php
                                $estados = $modelo->obtenerEstados();
                                foreach ($estados as $estado):
                                ?>
                                <option value="<?php echo htmlspecialchars($estado['estado']); ?>"><?php echo htmlspecialchars($estado['nombre_estado']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Guardar Rol</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Actualizar -->
    <div class="modal fade" id="modalActualizar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Actualizar Rol #<span id="display_id"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formActualizar">
                    <input type="hidden" name="id_rol" id="id_rol">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Nombre del Rol</label>
                            <input type="text" class="form-control p-3 border-light-subtle bg-light shadow-none" id="nombre_rol" name="nombre_rol" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Estado</label>
                            <select class="form-select p-3 border-light-subtle bg-light shadow-none" id="estado" name="estado" required>
                                <?php foreach ($estados as $estado): ?>
                                <option value="<?php echo htmlspecialchars($estado['estado']); ?>"><?php echo htmlspecialchars($estado['nombre_estado']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cerrar</button>
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
            const modalActualizar = document.getElementById('modalActualizar');
            modalActualizar.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const modal = this;
                
                const id = button.getAttribute('data-id_rol');
                modal.querySelector('#id_rol').value = id;
                modal.querySelector('#display_id').textContent = id;
                modal.querySelector('#nombre_rol').value = button.getAttribute('data-nombre_rol');
                modal.querySelector('#estado').value = button.getAttribute('data-estado');
            });

            // Form Ajax handlers
            $('#formCrear').on('submit', function(e) {
                e.preventDefault();
                $.post('../controladores/controlador_acc_rol.php?action=crear', $(this).serialize(), function(data) {
                    if(data) location.reload(); else alert('Error al crear el registro.');
                }, 'json');
            });

            $('#formActualizar').on('submit', function(e) {
                e.preventDefault();
                $.post('../controladores/controlador_acc_rol.php?action=actualizar', $(this).serialize(), function(data) {
                    if(data) location.reload(); else alert('Error al actualizar el registro.');
                }, 'json');
            });
        });

        function eliminar(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este rol?')) {
                $.post('../controladores/controlador_acc_rol.php?action=eliminar', {id: id}, function(data) {
                    if (data) location.reload(); else alert('Error al eliminar el rol.');
                }, 'json');
            }
        }
    </script>
</body>
</html>
