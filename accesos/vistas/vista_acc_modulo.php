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
    <title>Módulos del Sistema - Administrar</title>
    <?php include('../headIconos.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --accent-color: #20c997;
        }
        body { background-color: #f4f7f6; font-family: 'Inter', 'Segoe UI', sans-serif; }
        .dashboard-card { border: none; border-radius: 16px; background: #ffffff; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .header-gradient { background: var(--primary-gradient); color: white; border-radius: 16px 16px 0 0; padding: 25px; }
        .btn-gradient { background: var(--primary-gradient); color: white; border: none; border-radius: 10px; padding: 10px 20px; font-weight: 600; transition: all 0.3s ease; }
        .btn-gradient:hover { transform: scale(1.02); color: white; box-shadow: 0 5px 15px rgba(56, 239, 125, 0.4); }
        .table thead th { border-top: none; background: #fafbfc; color: #555; text-transform: uppercase; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; }
        .icon-preview { width: 40px; height: 40px; background: #e9ecef; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #495057; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row g-4">
            <div class="col-12">
                <div class="dashboard-card overflow-hidden">
                    <div class="header-gradient d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h4 mb-0 fw-bold">Módulos del Sistema</h2>
                            <p class="mb-0 opacity-75 small">Organiza los programas por categorías o módulos</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-white text-success fw-bold d-flex align-items-center gap-2 px-3 py-2 bg-white rounded-3 shadow-sm border-0" data-bs-toggle="modal" data-bs-target="#modalCrear">
                                <i class="fas fa-plus"></i> Nuevo Módulo
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-white text-muted fw-bold d-flex align-items-center gap-2 px-3 py-2 bg-white rounded-3 shadow-sm border-0" data-bs-toggle="dropdown">
                                    <i class="fas fa-file-export"></i> Exportar
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li><a class="dropdown-item py-2" href="../controladores/controlador_acc_modulo.php?action=exportar&formato=excel"><i class="icon-file-excel text-success me-2"></i> Excel</a></li>
                                    <li><a class="dropdown-item py-2" href="../controladores/controlador_acc_modulo.php?action=exportar&formato=csv"><i class="icon-doc-text text-info me-2"></i> CSV</a></li>
                                    <li><a class="dropdown-item py-2" href="../controladores/controlador_acc_modulo.php?action=exportar&formato=txt"><i class="icon-doc-inv text-secondary me-2"></i> TXT</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-white">
                        <form method="GET" action="../controladores/controlador_acc_modulo.php" class="mb-4">
                            <input type="hidden" name="action" value="buscar">
                            <div class="input-group shadow-sm rounded-3">
                                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="busqueda" class="form-control border-0 py-2 shadow-none" placeholder="Buscar módulo por nombre..." value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>">
                                <button type="submit" class="btn btn-light px-4 border-0">Buscar</button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th class="ps-3">ID</th>
                                        <th>Módulo</th>
                                        <th class="text-center">Icono</th>
                                        <th class="text-center">Orden</th>
                                        <th>Estado</th>
                                        <th class="text-center pe-3">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    require_once '../modelos/modelo_acc_modulo.php';
                                    $modelo = new ModeloAcc_modulo();
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
                                        <td class="ps-3 fw-bold text-muted"><?php echo htmlspecialchars($reg['id_modulo']); ?></td>
                                        <td class="fw-semibold text-dark"><?php echo htmlspecialchars($reg['nombre_modulo']); ?></td>
                                        <td class="text-center">
                                            <div class="icon-preview mx-auto">
                                                <i class="<?php echo htmlspecialchars($reg['icono']); ?> h5 mb-0"></i>
                                            </div>
                                        </td>
                                        <td class="text-center"><span class="badge bg-light text-muted border"><?php echo htmlspecialchars($reg['orden']); ?></span></td>
                                        <td>
                                            <span class="status-badge <?php echo ($reg['estado'] == 'A' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'); ?>">
                                                <?php echo htmlspecialchars($reg['nombre_estado']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center pe-3">
                                            <div class="btn-group shadow-sm">
                                                <button class="btn btn-sm btn-white text-primary border" data-bs-toggle="modal" data-bs-target="#modalActualizar" 
                                                data-id_modulo="<?= $reg['id_modulo'] ?>"
                                                data-nombre_modulo="<?= htmlspecialchars($reg['nombre_modulo']) ?>"
                                                data-icono="<?= htmlspecialchars($reg['icono']) ?>"
                                                data-orden="<?= $reg['orden'] ?>"
                                                data-estado="<?= $reg['estado'] ?>">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button class="btn btn-sm btn-white text-danger border" onclick="eliminar('<?= $reg['id_modulo'] ?>')">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted fst-italic">No se encontraron módulos registrados.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 d-flex justify-content-between align-items-center">
                            <form method="GET" class="d-flex align-items-center gap-2">
                                <span class="text-muted small">Registros:</span>
                                <select name="registrosPorPagina" class="form-select form-select-sm shadow-none" onchange="this.form.submit()" style="width: auto;">
                                    <?php foreach([10,30,50,100] as $r): ?>
                                        <option value="<?= $r ?>" <?= $registrosPorPagina == $r ? 'selected' : '' ?>><?= $r ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="pagina" value="1">
                            </form>
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <?php
                                    $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
                                    for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                        <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?>">
                                            <a class="page-link shadow-none" href="?pagina=<?= $i ?>&registrosPorPagina=<?= $registrosPorPagina ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear -->
    <div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg card">
                <div class="modal-header border-0 bg-success text-white">
                    <h5 class="modal-title fw-bold">Nuevo Módulo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCrear">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">NOMBRE DEL MÓDULO</label>
                            <input type="text" class="form-control p-3 bg-light border-0 shadow-none" name="nombre_modulo" required placeholder="Ej: Configuración, Reportes...">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-muted">ICONO (Fontello)</label>
                                <div class="input-group shadow-sm rounded-3">
                                    <input type="text" class="form-control p-3 bg-light border-0 shadow-none" name="icono" id="create_icono" placeholder="icon-cog-outline">
                                    <button type="button" class="btn btn-dark d-flex align-items-center px-3" onclick="openIconPicker('create_icono')" title="Seleccionar Icono">
                                        <i class="fas fa-icons me-1"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">ORDEN</label>
                                <input type="number" class="form-control p-3 bg-light border-0 shadow-none" name="orden" value="1">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">ESTADO</label>
                                <select class="form-select p-3 bg-light border-0 shadow-none" name="estado" required>
                                    <?php
                                    $estados = $modelo->obtenerEstados();
                                    foreach ($estados as $est): ?>
                                        <option value="<?= $est['estado'] ?>"><?= $est['nombre_estado'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success px-4">Crear Módulo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Actualizar -->
    <div class="modal fade" id="modalActualizar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg card">
                <div class="modal-header border-0 bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i> Actualizar Módulo #<span id="display_id"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formActualizar">
                    <input type="hidden" name="id_modulo" id="update_id">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">NOMBRE DEL MÓDULO</label>
                            <input type="text" class="form-control p-3 bg-light border-0 shadow-none" name="nombre_modulo" id="update_nombre" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-muted">ICONO</label>
                                <div class="input-group shadow-sm rounded-3">
                                    <input type="text" class="form-control p-3 bg-light border-0 shadow-none" name="icono" id="update_icono">
                                    <button type="button" class="btn btn-dark d-flex align-items-center px-3" onclick="openIconPicker('update_icono')" title="Seleccionar Icono">
                                        <i class="fas fa-icons me-1"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">ORDEN</label>
                                <input type="number" class="form-control p-3 bg-light border-0 shadow-none" name="orden" id="update_orden">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">ESTADO</label>
                                <select class="form-select p-3 bg-light border-0 shadow-none" name="estado" id="update_estado" required>
                                    <?php foreach ($estados as $est): ?>
                                        <option value="<?= $est['estado'] ?>"><?= $est['nombre_estado'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary px-4">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalUpd = document.getElementById('modalActualizar');
            modalUpd.addEventListener('show.bs.modal', function(event) {
                const btn = event.relatedTarget;
                const modal = this;
                const id = btn.getAttribute('data-id_modulo');
                modal.querySelector('#display_id').textContent = id;
                modal.querySelector('#update_id').value = id;
                modal.querySelector('#update_nombre').value = btn.getAttribute('data-nombre_modulo');
                modal.querySelector('#update_icono').value = btn.getAttribute('data-icono');
                modal.querySelector('#update_orden').value = btn.getAttribute('data-orden');
                modal.querySelector('#update_estado').value = btn.getAttribute('data-estado');
            });

            $('#formCrear').on('submit', function(e) {
                e.preventDefault();
                $.post('../controladores/controlador_acc_modulo.php?action=crear', $(this).serialize(), function(d) {
                    if(d) location.reload(); else alert('Error al crear módulo.');
                }, 'json');
            });

            $('#formActualizar').on('submit', function(e) {
                e.preventDefault();
                $.post('../controladores/controlador_acc_modulo.php?action=actualizar', $(this).serialize(), function(d) {
                    if(d) location.reload(); else alert('Error al actualizar módulo.');
                }, 'json');
            });
        });

        function eliminar(id) {
            if(confirm('¿Seguro que deseas eliminar este módulo?')) {
                $.post('../controladores/controlador_acc_modulo.php?action=eliminar', {id: id}, function(d) {
                    if(d) location.reload(); else alert('No se pudo eliminar el módulo.');
                }, 'json');
            }
        }
    </script>
    <?php include('icon_picker.php'); ?>
</body>
</html>
